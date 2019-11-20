<?php
    /**
     * Created by PhpStorm.
     * User: walker
     * Date: 2019/10/23
     * Time: 15:56
     */

    namespace App\Dictionary;
    class Code
    {
        public static $com
            = [
                /**
                 * 2**
                 */
                200  => '成功',
                /*
                 * 4***
                 */
                4001 => '缺少必要参数',
                4002 => '添加失败',
                4003 => '修改失败',
                4004 => '删除失败',
            ];
        public static $user
            = [
                'get_user_info_success' => ['code' => 200, 'msg' => '获取用户信息成功'],
                'get_user_info_fail'    => ['code' => 4001, 'msg' => '获取用户信息失败'],
                'login_fail'            => ['code' => 4003, 'msg' => '用户账户密码错误'],
                'not_login'             => ['code' => 4004, 'msg' => '用户未登录'],
            ];

    }
