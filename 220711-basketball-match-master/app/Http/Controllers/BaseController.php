<?php


namespace App\Http\Controllers;


use App\Models\System\SystemDict;
use App\User;
use Dingo\Api\Routing\Helpers;
use \Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class BaseController extends Controller
{
    use Helpers;


    protected $user;

    public function __construct()
    {
        if ($user = auth()->user()) {
            if ($user->status == 0) {
                auth()->logout();
                echo json_encode([
                    'status' => 0,
                    'code' => 400,
                    'msg' => '账户已冻结',
                ]);
                exit;
            }
        }
        $token = request()->get('token');
        if ($token) {
            $user = User::query()->where([
                'token' => $token
            ])->first();
            if (!$user){
                header('Content-Type:  application/json');
                echo json_encode([
                    'status' => 0,
                    'code' => 401,
                    'msg' => '未查找用户',
                ]);
                exit;
            }
            if ($user->token_expire_at < strtotime(date('Y-m-d H:i:s'))) {
                header('Content-Type:  application/json');
                echo json_encode([
                    'status' => 0,
                    'code' => 401,
                    'msg' => '当前令牌已失效',
                ]);
                exit;
            }
            $this->user = $user;
        }

        $this->initialize();
    }

    protected function initialize()
    {

    }

    public function success($data = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 1,
            'code' => 200,
            'msg' => 'Successfully',
            'data' => $data,
        ]);
    }

    public function successPage(LengthAwarePaginator $paginator): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 1,
            'code' => 200,
            'msg' => 'Successfully',
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
            ],
            'data' => $paginator->items(),
        ]);
    }

    public function error_400($msg): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 0,
            'code' => 400,
            'msg' => $msg,
//            'data' => [],
        ]);
    }

    public function error_500($msg): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 0,
            'code' => 500,
            'msg' => $msg,
//            'data' => [],
        ]);
    }
}
