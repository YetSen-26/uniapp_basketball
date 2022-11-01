<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'domain' => config('admin.route.domain'),
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
//    $router->resource('auth/users', 'AdminUserController');
    //轮播图
//    $router->resource('banner', 'BannerController');
    //账号管理
    $router->resource('users', 'UserController');
    //赛事分类
    $router->resource('category', 'CategoryController');
    //赛事管理
    $router->resource('competition', 'CompetitionController');
    $router->resource('competitionApply', 'CompetitionApplyController');
    $router->resource('matchCategory', 'MatchCategoryController');
    $router->resource('matchUser', 'MatchUserController');
    //比赛列表
    $router->resource('match', 'MatchController');
    //分类管理
    $router->resource('category', 'CategoryController');

    //商品管理
    $router->resource('goods', 'GoodsController');
    //订单管理
    $router->resource('order', 'OrderController');

    //签约球员管理
    $router->resource('contract', 'ContractController');

    //排行榜分类
    $router->resource('ranking-category', 'RankingCategoryController');
    //排行榜列表
    $router->resource('ranking-list', 'RankingListController');

    //意见反馈
    $router->resource('suggest', 'SuggestController');
    //系统设置
    $router->resource('setting', 'SettingController');

    $router->get('api/matchCategory', 'ApiController@matchCategory');
    $router->get('api/cacheMatchCreateInfo', 'ApiController@cacheMatchCreateInfo');
});
