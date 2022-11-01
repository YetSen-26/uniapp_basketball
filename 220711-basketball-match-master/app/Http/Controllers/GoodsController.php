<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\GoodsOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Yansongda\Pay\Gateways\Wechat\Support;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class GoodsController extends BaseController
{

    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['wechatNotify']]);
        parent::__construct();

    }

    //商品列表
    public function index(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'type' => 'required',
        ], [
            'type.required' => '商品类型必传',
        ]);
        if ($valid->fails()) {
            return $this->error_400($valid->errors()->first());
        }

        if (!in_array($request->get('type'), ['cash', 'gold'])) {
            return $this->error_400('商品类型错误');
        }

        $q = Goods::query();
        $q->where('type', $request->get('type'));
        if ($request->get('query')) {
            $q->where('goods_name', 'like', '%' . $request->get('query') . '%');
        }
        $list = $q
            ->select([
                'id',
                'goods_name',
                'price',
                'cover_path',
                'type',
            ])
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->paginate($request->get('pageSize'));

        return $this->successPage($list);
    }

    public function show(Request $request)
    {
        $q = Goods::query();

        $list = $q
            ->where(['id' => $request->get('id')])
            ->first();

        return $this->success($list);
    }

    public function orderList(Request $request)
    {
        $q = GoodsOrder::query();
        $q->where(['user_id' => auth()->id()])
            ->where('status', '=', 1);

        if ($request->get('type')) {
            $q->where(['type' => $request->get('type')]);
        }

        if ($request->get('order_no')) {
            $q->where('order_no', 'like', '%' . $request->get('order_no') . '%');
        }

        $list = $q
            ->orderByDesc('id')
            ->paginate($request->get('pageSize'));

        return $this->successPage($list);
    }

    public function orderShow(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'id.required' => '订单ID必传',
        ]);
        if ($valid->fails()) {
            return $this->error_400($valid->errors()->first());
        }

        $q = GoodsOrder::query();
        $q->with(['goods']);
        $q->where(['user_id' => auth()->id()])
            ->where(['id' => $request->get('id')]);

        $list = $q->first();

        return $this->success($list);
    }

    public function orderStore(Request $request)
    {
        if (!$request->get('goods_id')) {
            return $this->error_400('商品ID必传');
        }
        $num = (int)$request->get('num');

        if (empty($num)) {
            return $this->error_400('购买数量必传');
        } elseif (!is_int($num)) {
            return $this->error_400('购买数量必须为整数');
        }

        $goods = Goods::query()->find($request->get('goods_id'));
        if ($goods->type == 'cash') {
            $totalAmount = $goods->price * $num;
            $status = 0;
            $orderNo = "CS" . date('YmdHis') . rand(10000, 99999);
        } else {
            $totalAmount = $goods->price * $num;
            $status = 1;
            $orderNo = "GL" . date('YmdHis') . rand(10000, 99999);
            if ($totalAmount > auth()->user()->gold_cnt) {
                return $this->error_400('账户金币余额不足');
            }
        }

        $q = new GoodsOrder();

        $q->fill([
            'order_no' => $orderNo,
            'user_id' => auth()->id(),
            'type' => $goods->type,
            'price' => $goods->price,
            'num' => $request->get('num'),
            'total_amount' => $totalAmount,
            'goods_id' => $request->get('goods_id'),
            'status' => $status,
        ]);
        $q->save();

        if ($goods->type == 'cash') {
            $config = Config::get('pay.wechat');
            $config['notify_url'] = asset("api/goods/wechatNotify");

            $order = [
                'out_trade_no' => $orderNo,
                'body' => "商品购买",
                'total_fee' => (int)bcmul($totalAmount, 100, 0),
                'openid' => auth()->user()->openid,
            ];
            Log::debug("商城现金商品——支付前配置信息：", [$config['notify_url']]);
            Log::debug("商城现金商品——支付前订单信息：", $order);
            $res = Pay::wechat($config)->miniapp($order);
            Log::debug("商城现金商品——签名信息：", $res->toArray());
            $res = [
                'id' => $q->id,
                'order_no' => $orderNo,
                'sign' => $res->toArray()
            ];
        } else {
            $res = [
                'id' => $q->id,
                'order_no' => $orderNo,
            ];
        }

        return $this->success($res);
    }


    public function wechatNotify()
    {
        Log::debug("开始回调",[request()->all()]);
        $config = Config::get('pay.wechat');
        $pay = Pay::wechat($config);

        DB::beginTransaction();
        try {
            $data = $pay->verify();
            $data = $data->all();
//            $data = '{"appid":"wx39d5f6351d754ee7","bank_type":"OTHERS","cash_fee":"1","fee_type":"CNY","is_subscribe":"N","mch_id":"1610394055","nonce_str":"1K5v4IAHL1OkNDSn","openid":"oOUyU4lQUQDysUgCtoVx6fU2jBA8","out_trade_no":"CS2022081115283150062","result_code":"SUCCESS","return_code":"SUCCESS","sign":"F7A1E5EA745EE741B8050DE7444FC510","time_end":"20220811152838","total_fee":"1","trade_type":"JSAPI","transaction_id":"4200001611202208112634654168"}';
//            $data = json_decode($data,true);

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
            $order = GoodsOrder::where([
                'order_no' => $tradeNo,
                'total_amount' => $amount,
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
}
