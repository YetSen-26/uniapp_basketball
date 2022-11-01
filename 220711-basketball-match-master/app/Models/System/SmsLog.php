<?php


namespace App\Models\System;


use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasDateTimeFormatter;

    protected $guarded = [];
    public $timestamps = false;

    const VERIFY_Y = 1;
    const VERIFY_N = 0;
    const VERIFY_INVALID = -1;

    const USAGE_REGISTER = 'register';//用户注册
    const USAGE_FORGET = 'forget';//忘记密码
    const USAGE_BIND = 'bind';//绑定手机号

    const TEMP_SIMPLE = 'SMS_230585016';

    public static function reAll()
    {
        return implode(',', [
            self::USAGE_REGISTER,
            self::USAGE_FORGET,
            self::USAGE_BIND,
        ]);
    }

    /**
     * @param $mobile
     * @param $usage
     * @param $code
     */
    public static function verifyMobile($mobile, $code, $usage)
    {
        $smsLog = SmsLog::where([
            'mobile' => $mobile,
            'code' => $code,
            'is_verify' => self::VERIFY_N,
            'usage' => $usage,
        ])->whereRaw('invalid_time > ' . time())->first();

        if (!$smsLog) {
            return false;
        }

        $smsLog->is_verify = self::VERIFY_Y;
        $smsLog->save();

        return true;
    }

}
