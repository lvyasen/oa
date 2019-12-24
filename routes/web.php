<?php

    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */

    Route::get('/', function() {
        return view('welcome');
    });
    Route::any('test','\App\Http\Controllers\V1\GAController@gaTest');//测试站点

//    Route::any('pullProductsData','\App\Http\Controllers\Erp\ShopifyController@pullProductsData');//测试站点
    Route::any('oauthCallback','\App\Http\Controllers\V1\GAController@oauthCallback');//google回调站点
