<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Competition;
use App\Models\CompetitionApply;
use App\Models\CompetitionGuessing;
use App\Models\CompetitionMatch;
use App\Models\MatchCategory;
use App\Models\MatchTeam;
use App\Models\System\SystemUserGoldLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Yansongda\Pay\Pay;

class MatchController extends BaseController
{

    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['competitionList', 'wechatNotify']]);
        parent::__construct();
    }

    public function competitionCategory(Request $request)
    {
        $list = Category::query()
            ->select([
                'id',
                'name',
            ])
            ->where(['status' => 1])
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->get();
        return $this->success($list);
    }

    //赛事列表
    public function competitionList(Request $request)
    {
        $q = Competition::query();
        if ($request->get('only_valid') == 1) {
            $q->where('expiration_date', '>=', date('Y-m-d'));
        }
        if ($request->get('query')) {
            $q->where('competition_name', 'like', '%' . $request->get('query') . '%');
        }
        if ($request->get('category_id')) {
            $q->where('category_id', '=', $request->get('category_id'));
        }
        $list = $q->orderByDesc('begin_date')
            ->orderByDesc('id')
            ->get();
        return $this->success($list);
    }

    //赛事报名列表
    public function competitionApplyList(Request $request)
    {
        $q = CompetitionApply::query();
        $list = $q
            ->from('competition_apply', 'CAT')
            ->leftJoin('competition AS CT', 'CT.id', '=', 'CAT.competition_id')
            ->where([
                'user_id' => auth()->id(),
                'status' => 1
            ])
            ->select([
                'CAT.id',
                'CAT.order_no',
                'CAT.competition_id',
                'CAT.entry_fee',
                'CAT.deposit_fee',
                'CAT.created_at',
                'CT.competition_name',
                'CT.begin_date',
                'CT.end_date',
                'CT.venue_name',
                'CT.venue_address',
            ])
            ->paginate($request->get('pageSize'));

        return $this->successPage($list);
    }

    //赛事报名
    public function competitionApply(Request $request)
    {
        $q = CompetitionApply::query();
        $competitionApply = $q->where([
            'competition_id' => $request->get('competition_id'),
            'user_id' => auth()->id()
        ])
            ->first();
        if ($competitionApply && $competitionApply->status > 0) {
            return $this->error_400('赛事已报名');
        }
        $competition = Competition::query()
            ->where('expiration_date', '>=', date('Y-m-d'))
            ->where(['id' => $request->get('competition_id')])
            ->first();
        if (!$competition->id) {
            return $this->error_400('该赛事禁止报名');
        }
        $cnt = CompetitionApply::query()->where(['status' => 1])->count();
        if (!empty($competition->person_limit) && $cnt + 1 > $competition->person_limit) {
            return $this->error_400('该赛事报名人数已满不可报名');
        }

        if (!$competition->id && $competition->status == 0) {
            $orderNo = $competition->order_no;
        } else {
            $competitionApply = new CompetitionApply();
            $orderNo = "BM" . date('YmdHis') . rand(10000, 99999);
            $status = 0;
            if (empty($competition->entry_fee + $competition->deposit_fee)) {
                $status = 1;
            }
            $competitionApply->fill([
                'competition_id' => $request->get('competition_id'),
                'user_id' => auth()->id(),
                'entry_fee' => $competition->entry_fee,
                'deposit_fee' => $competition->deposit_fee,
                'status' => $status,
                'order_no' => $orderNo,
                'total_fee' => bcadd($competition->entry_fee, $competition->deposit_fee, 2),
            ]);
            $competitionApply->save();
        }
        if ($competitionApply->status == 1) {
            return $this->success([
                'id' => $competitionApply->id,
                'order_no' => $orderNo,
                'status' => $status,
                'sign' => '',
            ]);
        }

        $config = Config::get('pay.wechat');
        $config['notify_url'] = asset("api/match/wechatNotify");

        $order = [
            'out_trade_no' => $orderNo,
            'body' => "球赛报名费",
            'total_fee' => (int)bcmul($competitionApply->total_fee, 100, 0),
            'openid' => auth()->user()->openid,
        ];
        Log::debug("报名——支付前订单信息：", $order);
        $res = Pay::wechat($config)->miniapp($order);
        Log::debug("报名——签名信息：", $res->toArray());

        return $this->success([
            'id' => $competitionApply->id,
            'order_no' => $orderNo,
            'status' => 0,
            'sign' => $res->toArray(),
        ]);
    }

    public function wechatNotify()
    {
        Log::debug("开始回调", [request()->all()]);
        $config = Config::get('pay.wechat');
        $pay = Pay::wechat($config);

        DB::beginTransaction();
        try {
            $data = $pay->verify();
            $data = $data->all();
            Log::debug('Wechat notify [call-back]', [$data]);

            if ($config['miniapp_id'] != $data['appid'] || $config['mch_id'] != $data['mch_id']) {
                Log::debug("身份校验错误");
                return $this->error_400('身份校验错误');
            }

            $transactionId = $data['transaction_id'];   //微信支付单号
            $tradeNo = $data['out_trade_no'];     //交易单号
            $total_fee = $data['total_fee'];     //总金额
            //检测订单
            $amount = bcdiv($total_fee, 100, 2);
            $order = CompetitionApply::where([
                'order_no' => $tradeNo,
                'total_fee' => $amount,
            ])->first();

            if (!$order) {
                Log::debug("订单不存在");
                return $this->error_400('订单不存在');
            }

            if ($order->status != 0) {
                Log::debug("订单状态错误");
                return $this->error_400('订单状态错误');
            }

            //修改订单
            $order->status = 1;
            $order->call_back_at = date('Y-m-d H:i:s');
            $order->callback_data = json_encode($data);
            $order->callback_trade_no = $transactionId;
            $order->save();
            Log::debug('Wechat notify', ["over"]);
            DB::commit();
            return $pay->success()->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
        }

        return Response::make('fail');
    }

    public function matchCategory(Request $request)
    {
        if (!$request->get('competition_id')) {
            return $this->error_400('赛事ID必传');
        }

        $list = MatchCategory::query()
            ->select([
                'id',
                'name',
            ])
            ->where([
                'status' => 1,
                'competition_id' => $request->get('competition_id'),
            ])
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->get();
        return $this->success($list);
    }

    //赛程列表
    public function matchList(Request $request)
    {
        if (!$request->get('competition_id')) {
            return $this->error_400('赛事ID必传');
        }

        $q = CompetitionMatch::query();
        $q->from('competition_match', 'CMT')
            ->leftJoin('match_category AS MC', 'MC.id', '=', 'CMT.match_category_id')
            ->leftJoin('match_team AS MTA', 'MTA.id', '=', 'CMT.team_a_id')
            ->leftJoin('match_team AS MTB', 'MTB.id', '=', 'CMT.team_b_id')
            ->leftJoin('competition_guessing AS TCG', function ($join) {
                $join->on('TCG.match_id', '=', 'CMT.id')
                    ->where('TCG.user_id', '=', auth()->id());
            });
        $q->where(['CMT.competition_id' => $request->get('competition_id')]);
        if ($request->get('search')) {
            $q->where(function ($query) use ($request) {
                $query->where('MTA.team_name', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('MTB.team_name', 'like', '%' . $request->get('search') . '%');
            });
        }
        if ($request->get('is_guessing') == 1) {
            $q->whereNotNull('TCG.status');
        } else if ($request->get('is_guessing') == 2) {
            $q->whereNull('TCG.status');
        }
        if ($request->get('match_category_id')) {
            $q->where(['match_category_id' => $request->get('match_category_id')]);
        }
        $list = $q->select([
            'CMT.ID',
            'MC.name AS match_category_name',
            'CMT.c_date',
            'CMT.match_date_begin',
            'CMT.match_date_end',
            'CMT.status',
            'MTA.team_name AS team_a_name',
            'MTA.grade AS team_a_grade',
            'MTA.logo_path AS team_a_logo_path',
            'MTB.team_name AS team_b_name',
            'MTB.grade AS team_b_grade',
            'MTB.logo_path AS team_b_logo_path',
            DB::raw('IFNULL(TCG.status,-99) AS guessing_status'),
        ])
            ->orderByDesc('id')
            ->get();

        return $this->success($list);
    }

    //比赛详情
    public function matchShow(Request $request)
    {
        if (!$request->get('match_id')) {
            return $this->error_400('比赛ID必传');
        }

        $cm = CompetitionMatch::query()
            ->from('competition_match', 'CMT')
            ->leftJoin('match_category AS MC', 'MC.id', '=', 'CMT.match_category_id')
            ->where(['CMT.id' => $request->get('match_id')])
            ->select([
                'CMT.ID',
                'MC.name AS match_category_name',
                'CMT.competition_id',
                'CMT.c_date',
                'CMT.match_date_begin',
                'CMT.match_date_end',
                'CMT.status',
                'CMT.team_a_id',
                'CMT.team_b_id',
            ])
            ->first();

        $t_a = MatchTeam::query()
            ->select([
                'team_name',
                'logo_path',
                'grade',
            ])
            ->find($cm->team_a_id);

        $t_b = MatchTeam::query()
            ->select([
                'team_name',
                'logo_path',
                'grade',
            ])
            ->find($cm->team_b_id);

        $t_a->member_list = User::query()
            ->from('users', 'TU')
            ->join('match_team_user AS TMTU', 'TMTU.user_id', '=', 'TU.id')
            ->join('user_grade as TUG', function ($join) {
                $join->on('TUG.owner_team_id', '=', 'TMTU.team_id')
                    ->on('TUG.user_id', '=', 'TU.id');
            })
            ->select([
                'TU.id',
                'TU.nickname',
                'TU.birthday',
                'TU.weight',
                'TU.height',
                'TU.desc',
                DB::raw('YEAR(now())-YEAR(birthday) AS age'),
                'TUG.total_pointer',
                'TUG.two_pointer',
                'TUG.three_pointer',
                'TUG.four_pointer',
                'TUG.penalty_cnt',
                'TUG.rebound_cnt',
                'TUG.assist_cnt',
            ])
            ->where(['TMTU.team_id' => $cm->team_a_id])
            ->get();

        $t_b->member_list = User::query()
            ->from('users', 'TU')
            ->join('match_team_user AS TMTU', 'TMTU.user_id', '=', 'TU.id')
            ->join('user_grade as TUG', function ($join) {
                $join->on('TUG.owner_team_id', '=', 'TMTU.team_id')
                    ->on('TUG.user_id', '=', 'TU.id');
            })
            ->select([
                'TU.id',
                'TU.nickname',
                'TU.birthday',
                'TU.weight',
                'TU.height',
                'TU.desc',
                DB::raw('YEAR(now())-YEAR(birthday) AS age'),
                'TUG.total_pointer',
                'TUG.two_pointer',
                'TUG.three_pointer',
                'TUG.four_pointer',
                'TUG.penalty_cnt',
                'TUG.rebound_cnt',
                'TUG.assist_cnt',
            ])
            ->where(['TMTU.team_id' => $cm->team_b_id])
            ->get();
        $cm->team_a = $t_a;
        $cm->team_b = $t_b;

        return $this->success($cm);
    }

    //竞猜
    public function guessingStore(Request $request)
    {
        if (!$request->get('competition_id')) {
            return $this->error_400('赛事ID必传');
        }

        if (!$request->get('match_id')) {
            return $this->error_400('比赛ID必传');
        }

        $cm = CompetitionMatch::query()->find($request->get('match_id'));
        if ($cm->status != 0) {
            return $this->error_400('竞猜失败，比赛非未开始状态');
        }

        $q = CompetitionGuessing::query()
            ->firstOrNew([
                'competition_id' => $request->get('competition_id'),
                'match_id' => $request->get('match_id'),
                'user_id' => auth()->id(),
            ]);
        if ($q->id) {
            return $this->error_400('用户已竞猜不可重复竞猜');
        }

        $user = auth()->user();
        $goldCnt = (int)$request->get('gold_cnt');
        if (empty($goldCnt)) {
            return $this->error_400('竞猜金币数必传');
        } elseif (!is_int($goldCnt)) {
            return $this->error_400('竞猜金币数必须为数字');
        } elseif ($user->gold_cnt < $goldCnt) {
            return $this->error_400('账户剩余金币数不足');
        }
        if (!$request->get('win_team_id')) {
            return $this->error_400('竞猜球队ID不能为空');
        }

        $q->match_id = $request->get('match_id');
        $q->win_team_id = $request->get('win_team_id');
        $q->status = 0;
        $q->gold_cnt = $goldCnt;
        $q->save();


        $user->gold_cnt -= $goldCnt;
        $user->save();

        $s = new SystemUserGoldLog();
        $s->fill([
            'gold_cnt' => $request->get('gold_cnt'),
            'from' => SystemUserGoldLog::FROM_GUESSING,
            'balance' => SystemUserGoldLog::BALANCE_REDUCE,
            'rel_id' => $q->id
        ]);
        $s->save();

        return $this->success([
            'guessing_id' => $q->id,
        ]);
    }

    //竞猜列表
    public function guessingList(Request $request)
    {
        $q = CompetitionGuessing::query()
            ->from('competition_guessing', 'TCG')
            ->join('competition AS TC', 'TCG.competition_id', '=', 'TC.id')
            ->join('competition_match AS TCM', 'TCG.match_id', '=', 'TCM.id')
            ->leftJoin('match_team AS TMT_A', 'TMT_A.id', '=', 'TCM.team_a_id')
            ->leftJoin('match_team AS TMT_B', 'TMT_B.id', '=', 'TCM.team_b_id')
            ->select([
                'TC.competition_name',
                'TMT_A.team_name As team_a_name',
                'TMT_A.logo_path as team_a_log_path',
                'TMT_A.grade as team_a_grade',
                'TMT_B.team_name As team_b_name',
                'TMT_B.logo_path as team_b_log_path',
                'TMT_B.grade as team_b_grade',
                'gold_cnt',
                'win_gold_cnt',
                'TCG.status',
            ])
            ->orderByDesc('TCG.id')
            ->where(['TCG.user_id' => auth()->id()]);
        if ($request->get('query')) {
            $q->where(function ($q) use ($request) {
                $q->where('TMT_A.team_name', 'like', '%' . $request->get('query') . '%')
                    ->orWhere('TMT_B.team_name', 'like', '%' . $request->get('query') . '%');
            });
        }
        $list = $q->paginate($request->get('pageSize'));

        return $this->successPage($list);
    }

    //竞猜结果
    public function guessingShow(Request $request)
    {
        if (!$request->get('match_id')) {
            return $this->error_400('比赛ID必传');
        }

        $q = CompetitionMatch::query()
            ->from('competition_match', 'TCM')
            ->leftJoin('competition AS TC', 'TCM.competition_id', '=', 'TC.id')
            ->leftJoin('match_team AS TMT_A', 'TMT_A.id', '=', 'TCM.team_a_id')
            ->leftJoin('match_team AS TMT_B', 'TMT_B.id', '=', 'TCM.team_b_id')
            ->select([
                'TC.competition_name',
                'TCM.team_a_id As team_a_id',
                'TMT_A.team_name As team_a_name',
                'TMT_A.logo_path as team_a_log_path',
                'TMT_A.grade as team_a_grade',
                'TCM.team_b_id As team_b_id',
                'TMT_B.team_name As team_b_name',
                'TMT_B.logo_path as team_b_log_path',
                'TMT_B.grade as team_b_grade',
                DB::raw("(select count(1) from competition_guessing TCGIA where TCGIA.match_id = TCM.id and TCGIA.win_team_id = TCM.team_a_id) AS team_a_guessing_cnt"),
                DB::raw("(select count(1) from competition_guessing TCGIA where TCGIA.match_id = TCM.id and TCGIA.win_team_id = TCM.team_b_id) AS team_b_guessing_cnt")
            ])
            ->where(['TCM.id' => $request->get('match_id')])
            ->first();
        if (!$q) {
            return $this->error_400('未找到比赛数据');
        }

        $me = CompetitionGuessing::query()->where(['match_id' => $request->get('match_id'), 'user_id' => auth()->id()])->first();
        if ($me) {
            $q->gold_cnt = $me->gold_cnt;
            $q->win_gold_cnt = $me->win_gold_cnt;
            $q->guessing_status = $me->status;
        } else {
            $q->gold_cnt = 0;
            $q->win_gold_cnt = 0;
            $q->guessing_status = -99;
        }

        return $this->success($q);
    }
}
