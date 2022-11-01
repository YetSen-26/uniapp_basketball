<?php

namespace App\Http\Middleware;

use Closure;

class PlatFormFilter
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $platform = request()->header('platform');

        if (request()->hasHeader('platform') && request()->hasHeader('version')){
            $version = request()->header('version');
            $localVersion = '';
            switch ($platform){
                case 'ios':
                    $localVersion = env('API_VERSION_IOS');
                    break;
                case 'android':
                    $localVersion = env('API_VERSION_ANDROID');
                    break;
            }

            if (!empty($version) && $version < $localVersion) {
                return response()->json([
                    'status' => 0,
                    'code' => 400,
                    'msg' => '应用版本过低，请升级版本',
                ]);
            }
        }
        return $next($request);
    }
}
