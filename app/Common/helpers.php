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

                    $tree[] = [
                        'menu_id'   => $v['menu_id'],
                        'level'     => $level,
                        'menu_name' => $v['menu_name'],
                        'url'       => $v['url'],
                        'icon'      => $v['icon'],
                        'pid'       => $v['pid'],
                        'add_time'  => $v['add_time'],
                        'status'    => $v['status'],
                        'sort'      => $v['sort'],
                        'children'  => getMenuTree($data, $v['menu_id'], $level + 1),
                    ];
                }
            }
        }
        return $tree;
    }

    /**
     * 获取部门列表
     *
     * @param array $data
     * @param int   $parent_id
     * @param int   $level
     *
     * @return array
     * getDepartmentTree
     * author: walker
     * Date: 2019/11/22
     * Time: 15:38
     * Note:
     */
    function getDepartmentTree($data = [], $parent_id = 0, $level = 0)
    {
        $tree = [];
        if ($data && is_array($data)){
            foreach ($data as $v) {
                if ($v['pid'] == $parent_id){
                    $tree[] = [
                        'department_id'      => $v['department_id'],
                        'department_name'    => $v['department_name'],
                        'department_manager' => $v['department_manager'],
                        'department_num'     => $v['department_num'],
                        'pid'                => $v['pid'],
                        'desc'               => $v['desc'],
                        'level'              => $level,
                        'children'           => getDepartmentTree($data, $v['department_id'], $level + 1),
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

    /**
     * shopify回调
     * @param        $token
     * @param        $shop
     * @param        $api_endpoint
     * @param array  $query
     * @param string $method
     * @param array  $request_headers
     *
     * @return array|string
     * shopifyCall
     * author: walker
     * Date: 2019/12/7
     * Time: 9:38
     * Note:
     */
    function shopifyCall($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {

        // Build URL
        $url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
        if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);

        // Configure cURL
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
        // curl_setopt($curl, CURLOPT_SSLVERSION, 3);
        curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // Setup headers
        $request_headers[] = "";
        if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
            if (is_array($query)) $query = http_build_query($query);
            curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
        }

        // Send request to Shopify and capture any errors
        $response = curl_exec($curl);
        $error_number = curl_errno($curl);
        $error_message = curl_error($curl);

        // Close cURL to be nice
        curl_close($curl);

        // Return an error is cURL has a problem
        if ($error_number) {
            return $error_message;
        } else {

            // No error, return Shopify's response by parsing out the body and the headers
            $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

            // Convert headers into an array
            $headers = array();
            $header_data = explode("\n",$response[0]);
            $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
            array_shift($header_data); // Remove status, we've already set it above
            foreach($header_data as $part) {
                $h = explode(":", $part);
                $headers[trim($h[0])] = trim($h[1]);
            }

            // Return headers and Shopify's response
//            return array('headers' => $headers, 'response' => $response[1]);
            return json_decode($response[1],true);

        }

    }

