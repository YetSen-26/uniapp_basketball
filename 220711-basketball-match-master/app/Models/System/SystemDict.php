<?php

namespace App\Models\System;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

//系统设置
class SystemDict extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'system_dict';

    //签到金币
    const SYS_SIGNIN_GOLD = 'sign_in_gold';
    //竞猜获胜金币比例
    const SYS_GUESSING_WIN_GOLD_RATE = 'guessing_win_gold_rate';
    //赛程富文本
    const SYS_SCHEDULE_RULE = 'schedule_rule';
    //报名须知
    const SYS_APPLY_RULE = 'apply_rule';
    //签约球员
    const SYS_SIGN_CONTRACT_RULE = 'sign_contract_rule';

    protected $guarded = [];


    public static function getSystemDict($key = '')
    {
        $q = static::query();
        $data = [];
        if (empty(Cache::get('system_dict'))) {
            foreach ($q->get() as $item) {
                $data[$item->key] = $item->value;
            }
            Cache::add('system_dict', $data, 180);
        } else {
            $data = Cache::get('system_dict');
        }
        if (!empty($key) && isset($data[$key])) {
            return $data[$key];
        }
        return $data;
    }
}
