<?php

namespace App\Models\System;

use App\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

//用户金币日志表
class SystemUserGoldLog extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'system_user_gold_log';

    //签到
    const FROM_SIGN_IN = 'sign_in';
    //订单
    const FROM_ORDER = 'order';
    //竞猜
    const FROM_GUESSING = 'guessing';

    //增加
    const BALANCE_INCREASE = 1;
    //减少
    const BALANCE_REDUCE = -1;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
