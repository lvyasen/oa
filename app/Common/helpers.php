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
     * @return mixed|string
     * getIp
     * author: walker
     * Date: 2019/11/19
     * Time: 15:31
     * Note:
     */
    function getIp()
    {
        if ($_SERVER["HTTP_CLIENT_IP"] && strcasecmp($_SERVER["HTTP_CLIENT_IP"], "unknown")) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            if ($_SERVER["HTTP_X_FORWARDED_FOR"] && strcasecmp($_SERVER["HTTP_X_FORWARDED_FOR"], "unknown")) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else {
                if ($_SERVER["REMOTE_ADDR"] && strcasecmp($_SERVER["REMOTE_ADDR"], "unknown")) {
                    $ip = $_SERVER["REMOTE_ADDR"];
                } else {
                    if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'],
                                                                                                 "unknown")
                    ) {
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
     * @param array $data
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    function getTree($data = [], $parent_id = 0, $level = 0)
    {
        $tree = [];
        if ($data && is_array($data)) {
            foreach ($data as $v) {
                if ($v['parent_id'] == $parent_id) {
                    $tree[] = [
                        'id' => $v['id'],
                        'level' => $level,
                        'cat_name' => $v['cat_name'],
                        'parent_id' => $v['parent_id'],
                        'children' => getTree($data, $v['id'], $level + 1),
                    ];
                }
            }
        }
        return $tree;
    }



