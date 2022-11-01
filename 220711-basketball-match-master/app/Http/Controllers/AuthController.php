<?php

namespace App\Http\Controllers;

use App\Models\System\SignIn;
use App\Models\System\SmsLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['login', 'register', 'forget']]);

        parent::__construct();
    }

//    //用户注册
//    public function register(Request $request)
//    {
//        $valid = Validator::make($request->all(), [
//            'username' => 'required|string|unique:users',
//            'mobile' => 'required|string|unique:users',
//            'password' => 'string|min:6|max:10',
//        ], [
//            'mobile.required' => '手机号必填',
//            'mobile.unique' => '手机号已存在',
//            'password.required' => '密码必填',
//            'password.min' => '密码为6至10位',
//            'password.max' => '密码为6至10位',
//        ]);
//        if ($valid->fails()) {
//            return $this->error_400($valid->errors()->first());
//        }
//
//        if (!SmsLog::verifyMobile($request->get('mobile'), $request->get('code'), SmsLog::USAGE_REGISTER)) {
//            return $this->error_400('无效验证码');
//        }
//
//        $user = new User();
//        $user->username = $request->username;
//        $user->mobile = $request->mobile;
//        $user->password = bcrypt($request->password);
//        $user->save();
//        return $this->success();
//    }
//
//    //修改密码
//    public function editPassword(Request $request)
//    {
//        $user = auth()->user();
//        $user->password = bcrypt($request->password);
//        $user->save();
//        return $this->success();
//    }
//
//    //修改用户信息
    public function editUser(Request $request)
    {
        if ($request->has('nickname') && empty($request->get('nickname'))) {
            return $this->error_400('姓名不能为空');
        }

        if ($request->has('idcard') && empty($request->get('idcard'))) {
            return $this->error_400('身份证号不能为空');
        }

        $user = auth()->user();
        $data = [];
        if ($request->has('nickname')) {
            $data['nickname'] = $request->get('nickname');
        }
        if ($request->has('img_path')) {
            $data['img_path'] = $request->get('img_path');
        }
        if ($request->has('desc')) {
            $data['desc'] = $request->get('desc');
        }
        if ($request->has('weight')) {
            $data['weight'] = $request->get('weight');
        }
        if ($request->has('height')) {
            $data['height'] = $request->get('height');
        }
        if ($request->has('birthday')) {
            $data['birthday'] = $request->get('birthday');
        }
        if ($request->has('idcard')) {
            $data['idcard'] = $request->get('idcard');
        }
        if ($request->has('t_shirt_size')) {
            $data['t_shirt_size'] = $request->get('t_shirt_size');
        }
        if ($request->has('address')) {
            $data['address'] = $request->get('address');
        }
        if ($request->has('mobile')) {
            $data['mobile'] = $request->get('mobile');
        }
        if ($request->has('school')) {
            $data['school'] = $request->get('school');
        }
        $user->fill($data);

        if (!empty($user->idcard) && !empty($user->nickname)) {
            $t = explode(" ", microtime());
            $microsecond = round(round($t[1] . substr($t[0], 2, 3)));
            $checkData = [
                'appid' => "kbJoe8DNlX822ryn",
                'timestamp' => $microsecond,
                'idcard' => $user->idcard,
                'name' => $user->nickname,
            ];
            $signStr = md5($checkData['appid'] . "&" . $checkData['timestamp'] . "&" . "kbJoe8DNlX822rynbwOGoj5QbxsEDtlF");
            $checkData['sign'] = $signStr;
            $str = "";
            foreach ($checkData as $k => $v) {
                $str .= $k . "=" . $v . "&";
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.shumaidata.com/v4/id_card/check');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, trim($str, '&'));
            $content = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            Log::info($content);
            Log::error($error);
            $content = json_decode($content, true);
            Log::info("三方校验！", [$content]);
            if (isset($content['code']) && $content['code'] == 200) {
                if ($content['data']['desc'] != "一致") {
                    return $this->error_400('姓名与身份证号校验不一致，数据更新失败！');
                }
            } else {
                return $this->error_400('身份证号与姓名校验失败，数据更新失败');
            }
        }

        $user->save();

        return $this->success();
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();

        $signed = SignIn::query()
            ->where([
                'user_id' => $user->id,
                'date' => date('Y-m-d')
            ])->exists();

        $user->is_signed = $signed;
        $user->age = !empty($user->birthday) ? $this->getAgeByBirth($user->birthday, 2) : '';

        return $this->success($user);
    }

    function getAgeByBirth($date, $type = 1)
    {
        $nowYear = date("Y", time());
        $nowMonth = date("m", time());
        $nowDay = date("d", time());
        $birthYear = date("Y", strtotime($date));
        $birthMonth = date("m", strtotime($date));
        $birthDay = date("d", strtotime($date));
        if ($type == 1) {
            $age = $nowYear - ($birthYear - 1);
        } elseif ($type == 2) {
            if ($nowMonth < $birthMonth) {
                $age = $nowYear - $birthYear - 1;
            } elseif ($nowMonth == $birthMonth) {
                if ($nowDay < $birthDay) {
                    $age = $nowYear - $birthYear - 1;
                } else {
                    $age = $nowYear - $birthYear;
                }
            } else {
                $age = $nowYear - $birthYear;
            }
        }
        return $age;
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return $this->success();
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return $this->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * 忘记密码
     */
//    public function forget(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'code' => 'required|size:6',
//            'mobile' => 'required',
//            'new_password' => 'required|min:6|max:16'
//        ]);
//
//        if ($validator->fails()) {
//            return $this->error_400($validator->errors()->first());
//        }
//
//        //check mobile sms
//        $user = User::query()->where(['mobile' => $request->get('mobile')])->first();
//
//        if (!$user) {
//            return $this->error_400('用户不存在');
//        }
//
//        if (!SmsLog::verifyMobile($request->get('mobile'), $request->get('code'), SmsLog::USAGE_FORGET)) {
//            return $this->error_400('无效验证码');
//        }
//
//        $user->password = app('hash')->make($request->get('new_password'));
//        $user->save();
//        return $this->success();
//    }


    /**
     * @var \EasyWeChat\MiniProgram\Application
     */
    protected $app;

    public function initialize()
    {
        $this->app = Factory::miniProgram(config('wechat.mini_program.default'));
    }

    public function login(Request $request)
    {
        $code = $request->get('code');
        if (!$code) {
            return $this->error_400('登录code必传');
        }
        $sess = $this->app->auth->session($code);
        Log::debug('wechat session:', [$sess]);
        if (isset($sess['errcode']) && $sess['errcode'] != 0) {
            switch ($sess['errcode']) {
                case -1:
                    $msg = '系统繁忙';
                    break;
                case 40029:
                    $msg = 'code 无效';
                    break;
                case 45011:
                    $msg = '频率限制，每个用户每分钟100次';
                    break;
                case 40226:
                    $msg = '高风险等级用户，小程序登录拦截 。';
                    break;
                default:
                    $msg = $sess['errmsg'];
            }
            return $this->error_400($msg);
        }

        $user = User::query()->where(['openid' => $sess['openid']])->first();
        if (!$user) {
            $user = new User();
        }
        $user->openid = $sess['openid'];
        $user->session_key = $sess['session_key'];
        $user->unionid = isset($sess['unionid']) ? $sess['unionid'] : '';
        $user->save();

        $token = auth()->login($user);

        return $this->respondWithToken($token);
    }

    public function decryptData(Request $request)
    {
        if (!auth()->id()) {
            return $this->error_401('请登录!');
        }

        if (!$request->get('iv')) {
            return $this->error_400('请传递IV');
        }

        if (!$request->get('encryptedData')) {
            return $this->error_400('请传递加密数据');
        }

        $session = auth()->user()->session_key;
        $iv = $request->get('iv');
        $encryptedData = $request->get('encryptedData');
        $decryptedData = $this->app->encryptor->decryptData($session, $iv, $encryptedData);
        return $this->success($decryptedData);
    }

    public function bindMobile(Request $request)
    {
        if (!$request->get('mobile')) {
            return $this->error_400('手机号必传');
        }
        $user = auth()->user();
        $user->mobile = $request->get('mobile');
        $user->save();
        return $this->success();
    }
}
