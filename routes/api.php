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
        $api->post('editUserStatus', 'UsersController@editUserStatus');//修改用户状态
        $api->post('editUser', 'UsersController@editUser');//修改用户信息
        $api->post('getUserMenu', 'UsersController@getUserMenu');//获取用户菜单
        /**
         * 部门管理
         */
        $api->post('addDepartment', 'DepartmentController@addDepartment');//添加部门
        $api->post('delDepartment', 'DepartmentController@delDepartment');//删除部门
        $api->post('editDepartment', 'DepartmentController@editDepartment');//修改部门
        $api->post('getDepartmentAllList', 'DepartmentController@getDepartmentAllList');//修改部门
        /**
         * 权限管理
         */
        $api->post('addPermission', 'PermissionController@addPermission');//添加权限
        $api->post('editPermission', 'PermissionController@editPermission');//修改权限
        $api->post('editPermissionStatus', 'PermissionController@editPermissionStatus');//修改权限
        /**
         * 菜单管理
         */
        $api->post('addMenu', 'MenuController@addMenu');//添加菜单
        $api->post('getMenuList', 'MenuController@getMenuList');//菜单列表
        $api->post('editMenuStatus', 'MenuController@editMenuStatus');//修改菜单状态
        /**
         * 角色管理
         */
        $api->post('addRole', 'RoleController@addRole');//添加角色
        $api->post('getRoleInfo', 'RoleController@getRoleInfo');//获取角色信息
        $api->post('editRoleStatus', 'RoleController@editRoleStatus');//修改角色状态
        $api->post('editRole', 'RoleController@editRole');//修改角色
        $api->post('getRoleList', 'RoleController@getRoleList');//获取角色列表
        /**
         * 指标管理
         */
        $api->post('addQuota', 'QuotaController@addQuota');//添加部门指标
        $api->post('editQuota', 'QuotaController@editQuota');//修改部门指标
        $api->post('delQuota', 'QuotaController@delQuota');//删除部门指标
        $api->post('getQuotaList', 'QuotaController@getQuotaList');//获取部门列表
        /**
         * 站点管理
         */
        $api->post('addWebsite', 'WebsiteController@addWebsite');//添加站点
        $api->post('editWebsite', 'WebsiteController@editWebsite');//修改站点
        $api->post('getWebsiteList', 'WebsiteController@getWebsiteList');//获取站点列表
        $api->post('delWebsite', 'WebsiteController@delWebsite');//删除站点
        /**
         * Erp接口相关
         */
        $api->post('test', 'ErpController@test');//测试站点
        /**
         * 系统管理
         */
        $api->post('getSystemLog', 'SystemController@getSystemLog');//获取角色列表
    });
    /**
     * 无需用户信息
     */
    $api->version('v1', ['namespace' => '\App\Http\Controllers\V1'], function($api) {
        $api->post('signUp', 'AuthController@signUp');
        $api->post('login', 'AuthController@logIn');
        $api->post('getDepartmentList', 'DepartmentController@getDepartmentList');
    });
