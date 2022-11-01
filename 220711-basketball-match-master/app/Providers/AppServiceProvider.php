<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        if (env('APP_DEBUG')) {
            DB::listen(function ($query) {
                $bindings = $query->bindings;
                $sql = $query->sql;
                foreach ($bindings as $replace) {
                    $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }
                Log::info($sql);
            });
        }
    }
}
