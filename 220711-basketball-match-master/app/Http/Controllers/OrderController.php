<?php

namespace App\Http\Controllers;


use App\Models\System\Area;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yansongda\Pay\Pay;

class OrderController extends BaseController
{
    public function agentIndex()
    {
        $agents = Agent::query()
            ->where('level', '!=', 1)
            ->orderByDesc('money')
            ->get();

        $data = [];
        foreach ($agents as $agent) {
            $agent->money = floatval($agent->money);
            $data[] = $agent;
        }
        return $this->success($data);
    }

    public function orderCreate(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'agent_level' => 'required',
            'pay_from' => 'required',
        ], [
            'agent_level.required' => '代理等级必传',
            'pay_from.required' => '支付来源必传',
        ]);

        if ($valid->fails()) {
            return $this->error_400($valid->errors()->first());
        }
        $agent = Agent::query()->where(['level' => $request->get('agent_level')])->first();
        $order = new Order();
        $order->pay_from = $request->get('pay_from');
        $order->user_id = auth()->id();
        $order->order_no = Order::no();
        $order->amount = $agent->money;
        $order->month = $agent->month;
        $order->agent_level = $agent->level;
        $city_id = 0;
        if ($request->has('city_id') && $request->get('city_id')) {
            $city = Area::query()->where(['id' => request('city_id'), 'level' => 2])->first();
            if ($city && $city->agent_user_id > 0) {
                return $this->error_400('该城市已被代理');
            }

            $city_id = $request->get('city_id');
        }
        $order->agent_city_id = $city_id;
        $order->agent_level_name = $agent->name;
        $amount = $order->amount;
        if (env('PAY_TEST') == true) {
            $amount = '0.01';
        }
        $sign = '';
        if ($request->get('pay_from') == 'alipay') {
            //支付宝
            $sign = $this->aliPay($order->order_no, '会员充值', $amount);
        } else if ($request->get('pay_from') == 'wechat') {
            //微信
            $res = $this->wechatPay($order->order_no, '会员充值', $amount);
            if ($res) {
                $sign = $res;
            }
        }

        if ($sign) {
            $order->save();
        } else {
            return $this->error_400('签名失败');
        }

        return $this->success([
            'sign' => $sign
        ]);
    }

    /**
     * 微信支付
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function wechatPay($orderNo, $title, $amount, $prefixTitle = '推广充值——', $notifyUrl = '')
    {
        $config = Config::get('pay.wechat');
        if ($notifyUrl) {
            $config['notify_url'] = asset($notifyUrl);
        }

        $order = [
            'out_trade_no' => $orderNo,
            'body' => $prefixTitle . $title,
            'total_fee' => bcmul($amount, 100, 0),
            'sign_type' => 'MD5'
        ];

        $res = Pay::wechat($config)->app($order)->getContent();
        $res = json_decode($res, JSON_UNESCAPED_UNICODE);

        if (!$res) {
            return '';
        }

        $data = [
            'partnerid' => $config['mch_id'],   //商户号
            'appid' => $config['appid'],       //appid
            'prepayid' => $res['prepayid'],    //预订单id
            'noncestr' => Str::random(),     //随机串
            'timestamp' => time(),   //时间戳
            'package' => "Sign=WXPay",       //包
        ];

        $sign = Support::generateSign($data);
        $data['sign'] = $sign;
        return $data;
    }

    /**
     * 支付宝支付
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function aliPay($orderNo, $title, $amount, $prefixTitle = '推广充值——', $notifyUrl = '')
    {
        $config = Config::get('pay.alipay');
        if ($notifyUrl) {
            $config['notify_url'] = asset($notifyUrl);
        }

        $order = [
            'out_trade_no' => $orderNo,
            'subject' => $prefixTitle . $title,
            'total_amount' => $amount,
        ];
        $content = Pay::alipay($config)->app($order)->getContent();
        return $content;
    }

    /**
     * 充值订单列表
     */
    public function orderIndex(Request $request)
    {
        $q = Order::query()
            ->where([
                'user_id' => auth()->id(),
                'pay_status' => 1
            ]);
        $list = $q->orderByDesc('id')
            ->paginate(10);
        return $this->successPage($list);
    }

    public function aliNotify()
    {
        $config = Config::get('pay.alipay');
        $pay = Pay::alipay($config);
        $data = $pay->verify();
        $data = $data->all();
        Log::debug('Alipay notify [call-back]', $data);

        $transactionId = $data['trade_no'];   //阿里支付单号
        $tradeNo = $data['out_trade_no'];     //交易单号
        $total_fee = $data['total_amount'];     //总金额
        //检测订单
        $amount = $total_fee;
        if (env('PAY_TEST') == true) {
            $order = Order::where([
                'order_no' => $tradeNo,
            ])->first();
        } else {
            $order = Order::where([
                'order_no' => $tradeNo,
                'amount' => $amount,
            ])->first();
        }

        if (!$order) {
            return Response::make(
                Support::toXml(['return_code' => 'FAIL', 'return_msg' => "订单不存在"]),
                200,
                ['Content-Type' => 'application/xml']
            );
        }

        if ($order->pay_status != 0) {
            return Response::make(
                Support::toXml(['return_code' => 'FAIL', 'return_msg' => "订单状态错误" . $order->pay_status]),
                200,
                ['Content-Type' => 'application/xml']
            );
        }

        if ($this->payAgent($order, $data, $transactionId)) {
            return $pay->success();
        }
        return Response::make('fail');
    }

    protected function mapAgent($i)
    {
        $map = [
            1 => 0.3,
            2 => 0.2,
            3 => 0.1,
            4 => 0.05,
            5 => 0.03,
            6 => 0.02,
        ];
        if ($i > 0 && $i < 7) {
            return $map[$i];
        } else if ($i >= 7 && $i < 27) {
            return 0.01;
        }
        return 0;
    }

    public function wechatNotify()
    {
        $config = Config::get('pay.wechat');

        $pay = Pay::wechat($config);


        $data = $pay->verify();
        $data = $data->all();
        Log::debug('Wechat notify [call-back]', $data);

        if ($config['appid'] != $data['appid'] || $config['mch_id'] != $data['mch_id']) {
            return $this->error_400('身份校验错误');
        }

        $transactionId = $data['transaction_id'];   //微信支付单号
        $tradeNo = $data['out_trade_no'];     //交易单号
        $total_fee = $data['total_fee'];     //总金额
        //检测订单
        $amount = bcdiv($total_fee, 100, 2);
        if (env('PAY_TEST') == true) {
            $order = Order::where([
                'order_no' => $tradeNo,
            ])->first();
        } else {
            $order = Order::where([
                'order_no' => $tradeNo,
                'amount' => $amount,
            ])->first();
        }

        if (!$order) {
            return Response::make(
                Support::toXml(['return_code' => 'FAIL', 'return_msg' => "订单不存在"]),
                200,
                ['Content-Type' => 'application/xml']
            );
        }

        if ($order->pay_status != 0) {
            return Response::make(
                Support::toXml(['return_code' => 'FAIL', 'return_msg' => "订单状态错误" . $order->pay_status]),
                200,
                ['Content-Type' => 'application/xml']
            );
        }

        if ($this->payAgent($order, $data, $transactionId)) {
            return $pay->success();
        }


        return Response::make('fail');
    }

    protected function payAgent($order, $data, $transactionId)
    {
        DB::beginTransaction();
        try {
            //修改订单
            $order->pay_status = 1;
            $order->pay_time = date('Y-m-d H:i:s');
            $order->callback_data = json_encode($data);
            $order->callback_trade_no = $transactionId;
            $order->save();

            $user = User::query()->find($order->user_id);
            if ($order->month != 0) {
                if ($order->month == -1) {
                    $user->vip_type = 2;
                    $user->vip_limit_date = '2099-12-30';
                } else if ($order->month > 0) {
                    $user->vip_type = 1;
                    if (!empty($user->vip_limit_date)) {
                        $user->vip_limit_date = date('Y-m-d', strtotime("+$order->month month", strtotime($user->vip_limit_date)));
                    } else {
                        $user->vip_limit_date = date('Y-m-d', strtotime("+$order->month month"));
                    }
                }
            }
            if ($order->agent_level == -1) {
                $user->agent_level = $order->agent_level;

                $city = Area::query()->find($order->agent_city_id);
                $city->agent_user_id = $user->id;
                $city->save();
            } else if ($user->agent_level < $order->agent_level) {
                $user->agent_level = $order->agent_level;
            }
            $user->save();

            $parentId = $user->parent_id;
            $i = 1;
            $agentUserIds = Area::query()
                ->where(['level' => 2])
                ->where(['level' => 2])
                ->pluck('agent_user_id')
                ->toArray();
            $agentCityId = Area::query()->where([
                'id' => $user->city_id,
                'level' => 2
            ])->first();
            if ($agentCityId && $agentCityId->agent_user_id > 0) {
                $agentUser = User::query()->where('id', $agentCityId->agent_user_id)->first();
                if ($agentUser) {
                    $agentUser->money += 0.2 * $order->amount;
                    $agentUser->save();

                    $userMoneyLog = new UserMoneyLog();
                    $userMoneyLog->user_id = $agentUser->id;
                    $userMoneyLog->from_user_id = $order->user_id;
                    $userMoneyLog->type = UserMoneyLog::TYPE_MEMBER_PAY;
                    $userMoneyLog->desc = '会员充值城市代理提成';
                    $userMoneyLog->rate = 0.2;
                    $userMoneyLog->money = 0.2 * $order->amount;
                    $userMoneyLog->save();
                }
            }

            while ($parentId > 0 && $i < 27) {
                $pUser = User::query()->where(['id' => $parentId])->first();
                if ($pUser) {
                    if (!in_array($pUser->id, $agentUserIds) && $pUser->agent_level >= $i) {
                        $pUser->money += $this->mapAgent($i) * $order->amount;
                        $pUser->save();

                        $userMoneyLog = new UserMoneyLog();
                        $userMoneyLog->user_id = $pUser->id;
                        $userMoneyLog->from_user_id = $order->user_id;
                        $userMoneyLog->type = UserMoneyLog::TYPE_MEMBER_PAY;
                        $userMoneyLog->desc = '会员充值代理提成';
                        $userMoneyLog->rate = $this->mapAgent($i);
                        $userMoneyLog->money = $this->mapAgent($i) * $order->amount;
                        $userMoneyLog->save();
                    }
                    $parentId = $pUser->parent_id;
                } else {
                    $parentId = 0;
                }
                $i += 1;
            }
            Log::debug('Alipay notify', ["over"]);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
        return false;
    }

}
