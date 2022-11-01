<?php


namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\Ranking;
use App\Models\System\Area;
use App\Models\System\Banner;
use App\Models\System\SmsLog;
use App\Models\System\SystemDict;
use App\Models\System\SystemUserGoldLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Message;

class CommonController extends BaseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['initializeData', 'upload', 'category', 'uploadVideo', 'area', 'mapCity', 'sendMessCode']]);
        parent::__construct();

    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image',
        ]);
        if ($validator->fails()) {
            return $this->error_400($validator->errors()->first());
        }
        $file = $request->file('file');

        $filename = sha1($file->getClientOriginalName() . time() . rand(1000, 9999)) . '.' . $file->getClientOriginalExtension();
        $file->move('./uploads/', $filename);
        //获取所有数据
        $url = '/uploads/' . $filename;
        return $this->success([
            'url' => $url,
            'show_url' => asset($url)
        ]);
    }

    public function uploadVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->error_400($validator->errors()->first());
        }
        $file = $request->file('file');

        $filename = sha1($file->getClientOriginalName() . time() . rand(1000, 9999)) . '.' . $file->getClientOriginalExtension();
        $file->move('./uploads/video/', $filename);
        //获取所有数据
        $url = '/uploads/video/' . $filename;
        return $this->success([
            'url' => $url,
            'show_url' => asset($url)
        ]);
    }


    /**
     * 省份
     * @param Request $request
     */
    public function area(Request $request)
    {
        $q = Area::query()->where([
            'level' => $request->get('level'),
        ]);
        if ($request->has('parentid')) {
            $q->where('parentid', $request->get('parentid'));
        }
        $list = $q->select(['id', 'areaname', 'parentid', 'shortname'])
            ->orderBy('sort')
            ->get();
        return $this->success($list);
    }

    /**
     * 服务器当前时间
     */
    public function currentTime()
    {
        return $this->success([
            'time' => time(),
        ]);
    }

    /**
     * 初始化
     */
    public function initializeData()
    {
        $q = Ranking::query();
        $q->orderByDesc('id');
        $q->select([
            'id',
            'ranking_name'
        ]);
        $rankingCategory = $q->get();

        $data = [
            'time' => time(),
//            'banners' => Banner::query()->select(['id', 'img_path', 'url', 'content', 'sort'])->orderByDesc('sort')->get(),
            //签到金币数
            SystemDict::SYS_SIGNIN_GOLD => SystemDict::getSystemDict(SystemDict::SYS_SIGNIN_GOLD),
            //竞猜获胜金币比例
            SystemDict::SYS_GUESSING_WIN_GOLD_RATE => SystemDict::getSystemDict(SystemDict::SYS_GUESSING_WIN_GOLD_RATE),
            //赛程规则
            SystemDict::SYS_SCHEDULE_RULE => SystemDict::getSystemDict(SystemDict::SYS_SCHEDULE_RULE),
            // 报名须知
            SystemDict::SYS_APPLY_RULE => SystemDict::getSystemDict(SystemDict::SYS_APPLY_RULE),
            // 签到球员须知
            SystemDict::SYS_SIGN_CONTRACT_RULE => SystemDict::getSystemDict(SystemDict::SYS_SIGN_CONTRACT_RULE),
            //排行榜类型
            'ranking_category' => $rankingCategory,
            //字典
            'dict' => [
                //金币日志
                'gold-log' => [
                    'from' => [
                        SystemUserGoldLog::FROM_SIGN_IN => '签到',
                        SystemUserGoldLog::FROM_ORDER => '商城订单',
                        SystemUserGoldLog::FROM_GUESSING => '竞猜'
                    ],
                    'balance' => [
                        SystemUserGoldLog::BALANCE_INCREASE => '收入',
                        SystemUserGoldLog::BALANCE_REDUCE => '支出',
                    ],
                ],
            ],
        ];

        return $this->success($data);
    }


    public function mapCity(Request $request)
    {
        $area = Area::query()
            ->where('areaname', 'like', rtrim($request->get('areaname'), '市') . '%')
            ->first();

        return $this->success($area);
    }

    public function downloadInfo(Request $request)
    {
        return $this->success([]);
    }

    /**
     * 发送验证码
     * @param Request $request
     */
    public function sendMessCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'usage' => 'required|in:' . SmsLog::reAll()
        ], [
            'mobile:required' => '手机号必填',
            'usage:required' => '用途必传',
        ]);

        if ($validator->fails()) {
            return $this->error_400($validator);
        }

        while (true) {
            $code = rand(100000, 999999);
            $sms = SmsLog::query()->where([['invalid_time', '<', time()]])->firstOrNew([
                'code' => $code,
                'mobile' => $request->get('mobile'),
                'is_verify' => 0,
                'type' => 0,
                'usage' => $request->get('usage')
            ]);
            if (!$sms->id) {
                //设定失效时间
                $sms->invalid_time = time() + 60 * 15;
                $sms->save();
                break;
            }
        }
        $mess = new Message();
        switch ($request->get('usage')) {
            case SmsLog::USAGE_REGISTER:
                $mess->setData(['code' => $code]);
                $mess->setTemplate(SmsLog::TEMP_SIMPLE);
                break;
            case SmsLog::USAGE_BIND:
                $mess->setData(['code' => $code]);
                $mess->setTemplate(SmsLog::TEMP_SIMPLE);
                break;
            case SmsLog::USAGE_FORGET:
                $mess->setData(['code' => $code]);
                $mess->setTemplate(SmsLog::TEMP_SIMPLE);
                break;
        }
        $config = \Illuminate\Support\Facades\Config::get('sms');
        $easySms = new EasySms($config);
        $easySms->send($request->get('mobile'), $mess);

        return $this->success([
            'code' => $code,
            'usage' => $request->get('usage'),
            'invalid_time' => $sms->invalid_time
        ]);
    }


}
