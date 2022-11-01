<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json([
                'status' => 0, 'code' => '401', 'msg' => "无法解析token"
            ]);
        }

        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
            return response()->json([
                'status' => 0, 'code' => '401', 'msg' => "已失效的token",
            ]);
        }

        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json([
                'status' => 0, 'code' => '401', 'msg' => "已过期的token",
            ]);
        }

        if ($exception->getMessage() === 'Token not provided') {
            return response()->json([
                'status' => 0, 'code' => '401', 'msg' => "未发现token"
            ]);
        }

        return parent::render($request, $exception);
    }
}
