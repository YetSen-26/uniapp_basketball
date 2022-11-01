<?php

namespace App\Admin\Actions\Grid;

use App\Models\CompetitionGuessing;
use App\Models\CompetitionMatch;
use App\Models\System\SystemDict;
use App\Models\System\SystemUserGoldLog;
use App\User;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettleMatch extends RowAction
{
    /**
     * @return string
     */
    protected $title = '结算';

    public function __construct($title = null)
    {
        if ($title) {
            $this->title = $title;
        }
        parent::__construct($title);
    }

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $keys = $this->getKey();
        $c = CompetitionMatch::query()
            ->with(['teamA', 'teamB'])
            ->where('id', $keys)
            ->where(['status' => 2])->first();
        if (!$c) {
            return $this->response()->error('结算失败,竞赛状态错误')->refresh();
        }

        if (!empty($c->is_settle)) {
            return $this->response()->error('该赛事已结算，不可重复结算')->refresh();
        }

        if (empty($c->teamA->grade) && empty($c->teamB->grade)) {
            return $this->response()->error('请录入比赛结果')->refresh();
        }


        if ($c->teamA->grade == $c->teamB->grade) {
            $teamWinid = 0;
        } else if ($c->teamA->grade > $c->teamB->grade) {
            $teamWinid = $c->teamA->id;
        } else {
            $teamWinid = $c->teamB->id;
        }

        $cgs = CompetitionGuessing::query()
            ->where(['match_id' => $keys])
            ->where(['status' => 0])
            ->get();
//        Log::info('', [$cgs]);
        if (count($cgs)) {
            foreach ($cgs as $item) {
                if ($item->win_team_id == $teamWinid) {
                    $item->win_gold_cnt = floor($item->gold_cnt * SystemDict::getSystemDict(SystemDict::SYS_GUESSING_WIN_GOLD_RATE));
                    $item->status = 1;

                    $user = User::query()->find($item->user_id);
                    if ($user) {
                        $user->gold_cnt += $item->win_gold_cnt;
                        $user->save();

                        $ugl = new SystemUserGoldLog();
                        $ugl->fill([
                            'user_id' => $item->user_id,
                            'gold_cnt' => $item->win_gold_cnt,
                            'balance' => SystemUserGoldLog::BALANCE_INCREASE,
                            'from' => SystemUserGoldLog::FROM_GUESSING,
                            'rel_id' => $item->id
                        ]);
                        $ugl->save();
                    }
                    $item->save();

                } else {
                    $item->status = -1;
                    $item->save();
                }
            }
        }

        if ($c) {
            $c->is_settle = 1;
            $c->save();
        }
        return $this->response()->success('结算成功')->refresh();
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {

        return "请确认竞猜结果结算！";
    }

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [

        ];
    }
}
