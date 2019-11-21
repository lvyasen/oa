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


    $api = app('Dingo\Api\Routing\Router');
    /**
     * 需要用户信息
     */
    $api->version('v1', ['middleware' => 'api:auth', 'namespace' => '\App\Http\Controllers\V1'], function($api) {
        $api->post('addMenu', 'DepartmentController@addMenu');//添加菜单
        /**
         * 用户管理
         */
        $api->post('getUserList', 'UsersController@getUserList');//用户列表
        $api->post('getUserInfo', 'AuthController@getUserInfo');//获取用户信息
        $api->post('logout', 'AuthController@logout');//用户退出登录
        /**
         * 部门管理
         */
        $api->post('addDepartment', 'DepartmentController@addDepartment');//添加部门
        $api->post('delDepartment', 'DepartmentController@delDepartment');//删除部门
        $api->post('editDepartment', 'DepartmentController@editDepartment');//修改部门
        /**
         * 权限管理
         */
        $api->post('addPermission', 'PermissionController@addPermission');//添加权限
        /**
         * 菜单管理
         */
        $api->post('addMenu', 'MenuController@addMenu');//添加菜单
    });
    /**
     * 无需用户信息
     */
    $api->version('v1', ['namespace' => '\App\Http\Controllers\V1'], function($api) {
        $api->post('signUp', 'AuthController@signUp');
        $api->post('login', 'AuthController@logIn');
        $api->post('getDepartmentList', 'DepartmentController@getDepartmentList');
    });
