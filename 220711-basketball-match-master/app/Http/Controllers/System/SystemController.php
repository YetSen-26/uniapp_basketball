<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\BaseController;
use App\Models\System\SignIn;
use App\Models\System\Suggest;
use App\Models\System\SystemDict;
use App\Models\System\SystemUserGoldLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SystemController extends BaseController
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => []]);
        parent::__construct();
    }

    //签到
    public function signIn(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $goldCnt = SystemDict::getSystemDict(SystemDict::SYS_SIGNIN_GOLD) ?? 0;
        $signIn = SignIn::query()->firstOrNew(['user_id' => $userId, 'date' => date('Y-m-d')], ['gold_cnt' => $goldCnt]);
        if ($signIn->id) {
            return $this->error_400('用户今日已签到');
        }

        if ($signIn->save()) {
            $ugl = new SystemUserGoldLog();
            $ugl->fill([
                'user_id' => $userId,
                'gold_cnt' => $goldCnt,
                'balance' => SystemUserGoldLog::BALANCE_INCREASE,
                'from' => SystemUserGoldLog::FROM_SIGN_IN,
                'rel_id' => $signIn->id
            ]);
            $ugl->save();
        }

        $user->gold_cnt += $goldCnt;
        $user->save();

        return $this->success([
            'gold_cnt' => $user->gold_cnt
        ]);
    }

    //金币日志
    public function goldLog(Request $request)
    {
        $q = SystemUserGoldLog::query();
        $q->select([
            'gold_cnt',
            'from',
            'balance',
            'created_at'
        ]);

        if ($request->has('balance') && $request->get('balance') != '') {
            $q->where(['balance' => $request->get('balance')]);
        }

        if ($request->has('from') && $request->get('from') != '') {
            $q->where(['from' => $request->get('from')]);
        }

        $q->where(['user_id' => auth()->id()]);
        $q->orderByDesc('created_at');

        $list = $q->paginate($request->get('pageSize'));
        return $this->successPage($list);
    }

    /**
     * 反馈信息
     * @param Request $request
     */
    public function suggest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|max:255',
        ], [
            'content:required' => '反馈信息内容不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error_400($validator);
        }

        $suggest = new Suggest();
        $suggest->content = $request->get('content');
        $suggest->user_id = auth()->id();
        $suggest->save();

        return $this->success();
    }
}
