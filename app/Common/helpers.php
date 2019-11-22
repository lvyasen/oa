<?php
    /**
     * Created by PhpStorm.
     * User: walker
     * Date: 2019/10/22
     * Time: 17:26
     */

    function ajaxReturn($code, $msg = '', $data = [])
    {
        header('Content-type: application/json');
        $arr = [
            'status_code' => $code,
            'message'     => $msg,
            'data'        => $data,
        ];
        if (is_array($msg)){
            $arr['status_code'] = $msg['code'];
            $arr['message']     = $msg['msg'];
        }
        echo json_encode($arr, true);
        die();
    }

    function toArr($object)
    {
        //先编码成json字符串，再解码成数组
        return json_decode(json_encode($object), true);
    }

    /**
     * 获取客户端IP地址
     *
     * @return mixed|string
     * getIp
     * author: walker
     * Date: 2019/11/19
     * Time: 15:31
     * Note:
     */
    function getIp()
    {
        if ($_SERVER["HTTP_CLIENT_IP"] && strcasecmp($_SERVER["HTTP_CLIENT_IP"], "unknown")){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            if ($_SERVER["HTTP_X_FORWARDED_FOR"] && strcasecmp($_SERVER["HTTP_X_FORWARDED_FOR"], "unknown")){
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else {
                if ($_SERVER["REMOTE_ADDR"] && strcasecmp($_SERVER["REMOTE_ADDR"], "unknown")){
                    $ip = $_SERVER["REMOTE_ADDR"];
                } else {
                    if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'],
                                                                                                 "unknown")
                    ){
                        $ip = $_SERVER['REMOTE_ADDR'];
                    } else {
                        $ip = "unknown";
                    }
                }
            }
        }
        return ($ip);
    }

    /**
     * 无限极分类树 getTree($categories)
     *
     * @param array $data
     * @param int   $parent_id
     * @param int   $level
     *
     * @return array
     */
    function getMenuTree($data = [], $parent_id = 0, $level = 0)
    {
        $tree = [];
        if ($data && is_array($data)){
            foreach ($data as $v) {
                if ($v['pid'] == $parent_id){

                    $tree[]  = [
                        'menu_id'   => $v['menu_id'],
                        'level'     => $level,
                        'menu_name' => $v['menu_name'],
                        'url'       => $v['url'],
                        'icon'      => $v['icon'],
                        'pid'       => $v['pid'],
                        'sort'      => $v['sort'],
                        'children'  => getMenuTree($data, $v['menu_id'], $level + 1),
                    ];
                }
            }
        }
        return $tree;
    }
    function getDepartmentTree($data = [], $parent_id = 0, $level = 0)
    {
        $tree = [];
        if ($data && is_array($data)){
            foreach ($data as $v) {
                if ($v['pid'] == $parent_id){
                    $tree[]  = [
                        'department_id'   => $v['department_id'],
                        'department_name'   => $v['department_name'],
                        'department_manager'   => $v['department_manager'],
                        'department_num'   => $v['department_num'],
                        'pid'   => $v['pid'],
                        'level'     => $level,
                        'children'  => getMenuTree($data, $v['department_id'], $level + 1),
                    ];
                }
            }
        }
        return $tree;
    }

    /**
     * 循环获取子孙树 getSubTree($categories)
     *
     * @param array $data
     * @param int   $id
     * @param int   $level
     *
     * @return array
     */
    function getSubTree($id, $data = [], $pid = 0, $level = 0)
    {
        static $tree = [];

        foreach ($data as $key => $value) {
            if ($value['pid'] == $pid){
                $value['level'] = $level;
                $tree[]         = $value;
                getSubTree($id, $data, $value[$id], $level + 1);
            }
        }
        return $tree;
    }

    /**
     * 断点调试
     *
     * @param $data
     * fp
     * author: walker
     * Date: 2019/11/21
     * Time: 15:58
     * Note:
     */
    function fp($data)
    {
        echo "<pre>";
        print_r($data);
        die();
    }


