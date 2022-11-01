<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $app = app();
        $app['Dingo\Api\Http\RateLimit\Handler']->extend(function ($app) {
            return new \Dingo\Api\Http\RateLimit\Throttle\Authenticated;
        });
        app('Dingo\Api\Auth\Auth')->extend('jwt', function ($app) {
            return new \Dingo\Api\Auth\Provider\JWT($app['Tymon\JWTAuth\JWTAuth']);
        });
        $app['Dingo\Api\Transformer\Factory']->setAdapter(function ($app) {
            $fractal = new \League\Fractal\Manager;

            $fractal->setSerializer(new \League\Fractal\Serializer\JsonApiSerializer);

            return new \Dingo\Api\Transformer\Adapter\Fractal($fractal);
        });

        \Dingo\Api\Http\Response::addFormatter('json', new \Dingo\Api\Http\Response\Format\Jsonp);

        $app['Dingo\Api\Exception\Handler']->setErrorFormat([
            'error' => [
                'message' => ':message',
                'errors' => ':errors',
                'code' => ':code',
                'status_code' => ':status_code',
                'debug' => ':debug'
            ]
        ]);
    }
}
