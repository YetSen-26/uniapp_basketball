<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/**
 * @var Dingo\Api\Routing\Router $api
 */
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers'
], function ($api) {
    $api->group([
        'prefix' => 'auth',
    ], function ($router) {
//        $router->post('register', 'AuthController@register');
//        $router->post('editPassword', 'AuthController@editPassword');
        $router->post('editUser', 'AuthController@editUser');
        $router->post('login', 'AuthController@login');
        $router->post('logout', 'AuthController@logout');
//        $router->post('refresh', 'AuthController@refresh');
        $router->post('me', 'AuthController@me');
        $router->post('decryptData', 'AuthController@decryptData');
        $router->post('bindMobile', 'AuthController@bindMobile');
//        $router->post('forget', 'AuthController@forget');
//        $router->post('wx-auth', 'AuthController@wxauth');
    });

    $api->group([
        'prefix' => 'system',
        'namespace' => 'System'
    ], function ($router) {
        //签到
        $router->post('sign-in', 'SystemController@signIn');
        //金币日志
        $router->post('gold-log', 'SystemController@goldLog');
        //意见反馈
        $router->post('suggest', 'SystemController@suggest');
    });

    $api->group([
        'prefix' => 'user',
    ], function ($router) {
        //生涯-场均数据
        $router->post('avg-data', 'UserController@avgData');
        //生涯-历史战绩
        $router->post('history', 'UserController@history');
        //排行榜类型
        $router->post('ranking-category', 'UserController@rankingCategory');
        //排行榜列表
        $router->post('ranking-list', 'UserController@rankingList');
        //排行榜详情
        $router->post('ranking-show', 'UserController@rankingShow');
        //签约球员-列表
        $router->post('contract-list', 'UserController@contractList');
    });

    $api->group([
        'prefix' => 'goods',
    ], function ($router) {
        //商品列表
        $router->post('list', 'GoodsController@index');
        //商品详情
        $router->post('show', 'GoodsController@show');

        //订单列表
        $router->post('order-list', 'GoodsController@orderList');
        //订单详情
        $router->post('order-show', 'GoodsController@orderShow');
        //创建订单
        $router->post('order-store', 'GoodsController@orderStore');
        $router->post('wechatNotify', 'GoodsController@wechatNotify');
    });

    $api->group([
        'prefix' => 'match',
    ], function ($router) {
        //赛事分类
        $router->post('competition-category', 'MatchController@competitionCategory');
        //赛事列表
        $router->post('competition-list', 'MatchController@competitionList');
        //赛事报名列表
        $router->post('competition-apply-list', 'MatchController@competitionApplyList');
        //赛事报名
        $router->post('competition-apply', 'MatchController@competitionApply');
        // 报名支付回调
        $router->post('wechatNotify', 'MatchController@wechatNotify');

        //比赛类型
        $router->post('match-category', 'MatchController@matchCategory');
        //比赛列表
        $router->post('match-list', 'MatchController@matchList');
        //比赛详情
        $router->post('match-show', 'MatchController@matchShow');

        //竞猜
        $router->post('guessing-store', 'MatchController@guessingStore');
        //竞猜记录
        $router->post('guessing-list', 'MatchController@guessingList');
        //竞猜结果
        $router->post('guessing-show', 'MatchController@guessingShow');
    });


    $api->group([
        'prefix' => 'common',
    ], function ($router) {
        $router->post('upload', 'CommonController@upload');
//        $router->post('uploadVideo', 'CommonController@uploadVideo');
//        $router->post('area', 'CommonController@area');
        $router->post('currentTime', 'CommonController@currentTime');
        $router->post('initializeData', 'CommonController@initializeData');
//        $router->post('hotAndRecommend', 'CommonController@hotAndRecommend');
//        $router->post('mapCity', 'CommonController@mapCity');
//        $router->post('downloadInfo', 'CommonController@downloadInfo');
        $router->post('systemMsg', 'CommonController@systemMsg');
//        $router->post('sendMessCode', 'CommonController@sendMessCode');
//        $router->post('category', 'CommonController@category');
    });
});
