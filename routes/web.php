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

Route::get('/', function () {
    return view('welcome');
});
    Route::any('test',function(){
        $analyticsData = Analytics::fetchVisitorsAndPageViews(\Spatie\Analytics\Period::months(6));
//        $analyticsData = storage_path('app/analytics/service-account-credentials.json');
        fp($analyticsData);
    });//测试站点
