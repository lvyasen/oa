<?php

    use Illuminate\Http\Request;

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

    Route::middleware('auth:api')->get('/user', function(Request $request) {
        return $request->user();
    });
//    Route::group([
//                     'prefix' => 'auth',
//                 ], function() {
//
//        Route::post('logIn', '\App\Http\Controllers\V1\AuthController@logIn');
//        Route::post('signUp', '\App\Http\Controllers\V1\AuthController@signUp');
//
//
//        Route::group([
//                         'middleware' => 'auth:api',
//                     ], function() {
//            Route::get('logout', '\App\Http\Controllers\V1\AuthController@logout');
//            Route::post('getUserInfo', '\App\Http\Controllers\V1\AuthController@user');
//        });
//    });

    $api = app('Dingo\Api\Routing\Router');
    /**
     * 需要用户信息
     */
    $api->version('v1', ['middleware' => 'api:auth','namespace'=>'\App\Http\Controllers\V1'], function ($api) {
        $api->post('getUserInfo', 'AuthController@getUserInfo');
        $api->post('addMenu', 'MenuController@addMenu');//添加菜单
        /**
         * 操作相关
         */
        $api->post('addOperation', 'OperationController@addOperation');//添加操作
//        $api->post('addOperation', '\App\Http\Controllers\V1\OperationController@addOperation');//添加操作
    });
    /**
     * 无需用户信息
     */
    $api->version('v1', function ($api) {
        $api->post('signUp', '\App\Http\Controllers\V1\AuthController@signUp');
        $api->post('login', '\App\Http\Controllers\V1\AuthController@logIn');
        $api->post('getDepartmentList', '\App\Http\Controllers\V1\DepartmentController@getDepartmentList');
    });
