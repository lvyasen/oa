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
        $api->post('getDepartmentUsers', 'DepartmentController@getDepartmentUsers');//获取部门用户列表
        /**
         * 物料管理
         */
        $api->post('addMaterial', 'MaterialController@addMaterial');//添加物料
        $api->post('editMaterial', 'MaterialController@editMaterial');//添加物料
        $api->post('delMaterial', 'MaterialController@delMaterial');//删除物料
        $api->post('getMaterialList', 'MaterialController@getMaterialList');//获取物料列表
        $api->post('editMateriaStatus', 'MaterialController@editMateriaStatus');//物料审核

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
        $api->post('editMenu', 'MenuController@editMenu');//修改菜单状态
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
         * 用户指标管理
         */
        $api->post('addUserQuota', 'UserQuotaController@addUserQuota');//添加用户指标
        $api->post('editUserQuota', 'UserQuotaController@editUserQuota');//修改用户指标
        $api->post('getUserQuotaList', 'UserQuotaController@getUserQuotaList');//获取用户指标列表
        $api->post('delUserQuota', 'UserQuotaController@delUserQuota');//删除用户指标
        $api->post('getDepartmentQuotaList', 'UserQuotaController@getDepartmentQuotaList');//部门负责人指标
        $api->post('getDepartmentQuotaDetail', 'UserQuotaController@getDepartmentQuotaDetail');//部门负责人指标
        $api->post('getDepartmentQuotaAnalytics', 'UserQuotaController@getDepartmentQuotaAnalytics');//部门负责人指标
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
        $api->post('getPlatformUser', 'ErpController@getPlatformUser');//获取Erp站点列表
        $api->post('getWarehouse', 'ErpController@getWarehouse');//获取仓库列表
        $api->post('getEOrders', 'ErpController@getEOrders');//获取订单列表
        $api->post('getShippingMethod', 'ErpController@getShippingMethod');//获取运输方式
        $api->post('addLogistics', 'ErpController@addLogistics');//添加物流费用
        $api->post('editLogistics', 'ErpController@editLogistics');//修改物流费用
        $api->post('delLogistics', 'ErpController@delLogistics');//删除 物流费用
        $api->post('pullEPurchaseOrders', 'ErpController@pullEPurchaseOrders');//拉取采购单
        /**
         * 物流费用相关接口
         */
        $api->post('getShippingList', 'ErpController@getShippingList');//获取物流费用列表
        /**
         * GA接口相关
         */
        $api->post('getGaApiData', 'GAController@getGaApiData');//调用GA接口
        $api->post('getGaUserType', 'GAController@getGaUserType');//人群分析
        $api->post('getSourceMedium', 'GAController@getSourceMedium');//流量分析
        $api->post('addGaConfig', 'GAController@addGaConfig');//添加GA配置
        $api->post('editGaconfig', 'GAController@editGaconfig');//修改GA配置
        $api->post('delGaConfig', 'GAController@delGaConfig');//删除GA配置
        $api->post('getGaWebSitList', 'GAController@getGaWebSitList');//删除GA配置
        $api->post('getGaCommonApi', 'GAController@getGaCommonApi');//删除GA配置
        /**
         * Facebook相关接口
         */
        $api->post('getAds', 'FaceBookController@getAds');//删除GA配置
        /**
         * 系统管理
         */
        $api->post('getSystemLog', 'SystemController@getSystemLog');//获取角色列表
        /**
         * shopify
         */
        $api->any('addShopifyAuth', 'ShopifyController@addShopifyAuth');//添加shopif站点
        $api->any('getShopifyAuthList', 'ShopifyController@getShopifyAuthList');//添加shopif站点
        $api->any('countShopifyOrders', 'ShopifyController@countShopifyOrders');//添加shopif站点
        $api->any('countShopifyTodayOrders', 'ShopifyController@countShopifyTodayOrders');//统计shopify当天订单量
        /**
         * ERP
         */
        $api->any('getLogisticsList', 'ErpController@getLogisticsList');//获取物流列表
        $api->any('getLogisticsLineChart', 'ErpController@getLogisticsLineChart');//获取物流列表分析

    });
    /**
     * 无需用户信息
     */
    $api->version('v1', ['namespace' => '\App\Http\Controllers\V1'], function($api) {
        $api->post('signUp', 'AuthController@signUp');
        $api->post('login', 'AuthController@logIn');
        $api->post('getDepartmentList', 'DepartmentController@getDepartmentList');
        $api->any('pullEorders', 'ErpController@pullEorders');//获取订单列表
        $api->any('pullOrderCostDetail', 'ErpController@pullOrderCostDetail');//获取订单成本

        //        $api->post();
        //        $api->post('test', 'CommonController@test');//测试站点
        //  Route::any('shopify','\App\Http\Controllers\Erp\ShopifyController@countProducts');//测试站点
        //        $api->any('test',function(){
        ////            $analyticsData = Analytics::fetchVisitorsAndPageViews(\Spatie\Analytics\Period::days(7));
        //            $analyticsData = storage_path('app/analytics/service-account-credentials.json');
        //            fp($analyticsData);
        //                    });//测试站点

    });
    /**
     * 第三方接口
     */
    $api->version('v1', ['namespace' => '\App\Http\Controllers\Erp'], function($api) {
        /**
         * shopify相关接口
         */
        $api->post('getShopifyWebsite', 'WebSiteController@getShopifyWebsite');
        $api->any('pullProductsData', 'ShopifyController@pullProductsData');
        $api->any('getPrePullData', 'ShopifyController@getPrePullData');
        $api->any('getCustomersData', 'ShopifyController@getCustomersData');
        $api->post('getReport', 'ShopifyController@getReport');
        $api->any('shopifyInstall', 'ShopifyController@shopifyInstall');
        $api->any('shopifyGenerateToken', 'ShopifyController@shopifyGenerateToken');//获取token
    });
