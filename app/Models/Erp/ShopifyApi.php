<?php

    namespace App\Models\Erp;


    class ShopifyApi
    {
        //

        public $web_name = '';

        public $web_access = '';

        public $ab_name = '';

        public $web_id = '';

        public function __construct($params = [])
        {
            $this->initialize($params);
        }

        public function initialize($params = [])
        {
            $this->web_name   = $params['web_name'];
            $this->web_access = $params['web_access'];
            $this->ab_name    = $params['ab_name'];
            $this->web_id     = $params['web_id'];
            return $this;
        }

        /**
         * 商品统计
         *
         * @param int $down_time
         *
         * @return array|bool
         * products_count
         * author: walker
         * Date: 2019/11/27
         * Time: 15:49
         * Note:
         *
         */
        public function products_count($down_time = 0)
        {
            $t = time();

            $today = strtotime('today') - 3600 * 12;

            $start = date('Y-m-d\TH:i:s-04:00', $down_time ? ($down_time - 3600 * 12 - 60 * 20) : (strtotime('2017-10-01') - 3600 * 12));
            $end   = date('Y-m-d\TH:i:s-04:00', $today + 3600 * 24);
            $limit = 50;

            $url   = $this->web_access . '.myshopify.com/admin/products/count.json?created_at_min=' . $start . '&created_at_max=' . $end . '&published_status=any';
            $data  = $this->shopify_curl($url);
            $count = $data['count'];
            if ($count > 0){
                $max      = $limit;
                $page_max = ceil($count / $max);
                $arr      = [];
                for ($i = 1; $i <= $page_max; $i++) {
                    $arr[] = [
                        'shopify_url' => 'published_status=any&page=' . $i . '&created_at_min=' . $start . '&created_at_max=' . $end . '&limit=' . $max,
                        'add_time'    => $t,
                        'web_id'      => $this->web_id,
                        'type'        => 0,
                    ];
                }

                return array_reverse($arr);

            } else {
                return false;
            }
        }

        /**
         * 商品数据
         *
         * @param $params_url
         *
         * @return bool
         * products_data
         * author: walker
         * Date: 2019/11/27
         * Time: 15:50
         * Note:
         */
        public function products_data($params_url)
        {
            $url  = $this->web_access . '.myshopify.com/admin/products.json?' . $params_url;
            $data = $this->shopify_curl($url);

            if ($data['products']){
                $cont = $data['products'];
            } else {
                $cont = false;
            }

            return $cont;
        }

        /**
         * 订单统计
         *
         * @param int $down_time
         *
         * @return array|bool
         * order_count
         * author: walker
         * Date: 2019/11/27
         * Time: 15:50
         * Note:
         */
        public function order_count($down_time = 0)
        {
            $t = time();

            $today = strtotime('today') - 3600 * 12;

            $start = date('Y-m-d\TH:i:s-04:00', $down_time ? ($down_time - 3600 * 12 - 60 * 80) : (strtotime('2018-3-10') - 3600 * 12));
            $end   = date('Y-m-d\TH:i:s-04:00', $today + 3600 * 24);
            $limit = 100;

            $url   = $this->web_access . '.myshopify.com/admin/orders/count.json?updated_at_min=' . $start . '&updated_at_max=' . $end . '&status=any';
            $count = file_get_contents($url);
            $count = json_decode($count, true);
            $count = $count['count'];
            if ($count > 0){
                $max      = $limit;
                $page_max = ceil($count / $max);

                //循环每页的数据
                $arr = [];
                for ($i = 1; $i <= $page_max; $i++) {
                    $arr[] = [
                        'shopify_url' => 'status=any&page=' . $i . '&updated_at_min=' . $start . '&updated_at_max=' . $end . '&limit=' . $max,
                        'add_time'    => $t,
                        'web_id'      => $this->web_id,
                        'type'        => 0,
                    ];
                }
                return array_reverse($arr);

            } else {
                return false;
            }
        }


        /**
         * 订单数据
         *
         * @param $params_url
         *
         * @return bool
         * order_data
         * author: walker
         * Date: 2019/11/27
         * Time: 15:50
         * Note:
         */
        public function order_data($params_url)
        {
            $url = $this->web_access . '.myshopify.com/admin/orders.json?' . $params_url;

            $data = $this->shopify_curl($url);
            if ( !$data['errors']){
                $cont = $data['orders'];
            } else {
                $cont = false;
            }

            return $cont;
        }

        /**
         * 订单付款编号
         *
         * @param $shopify_id
         *
         * @return bool
         * order_payment_no
         * author: walker
         * Date: 2019/11/27
         * Time: 15:50
         * Note:
         */
        public function order_payment_no($shopify_id)
        {
            $url = $this->web_access . ".myshopify.com/admin/orders/{$shopify_id}/transactions.json";

            $data = $this->shopify_curl($url);
            if ($data['transactions']){
                foreach ($data['transactions'] as $row) {
                    if ($row['status'] == 'success'){
                        return $row;
                    }
                }
            }

            return false;
        }

        /**
         * 订单上传
         *
         * @param $shopify_id
         * @param $order
         *
         * @return bool
         * order_upload_shipping_no
         * author: walker
         * Date: 2019/11/27
         * Time: 15:51
         * Note:
         */
        public function order_upload_shipping_no($shopify_id, $order)
        {
            if ($this->web_id == 2){
                $location_id = 5983862844;
            } elseif ($this->web_id == 1) {
                $location_id = 1634795550;
            } elseif ($this->web_id == 3) {
                $location_id = 10609524793;
            } elseif ($this->web_id == 4) {
                $location_id = 18461392963;
            } elseif ($this->web_id == 5) {
                $location_id = 19967311985;
            } elseif ($this->web_id == 6) {
                $location_id = 19472416868;
            }

            $url = $this->web_access . ".myshopify.com/admin/orders/{$shopify_id}/fulfillments.json";

            $fulfillment = [
                'fulfillment' => [
                    'location_id'      => $location_id,
                    'tracking_number'  => $order['shipping_no'],
                    'tracking_url'     => $order['shipping_url'],
                    'tracking_company' => 'Other',
                    'notify_customer'  => true,
                ],
            ];
            $data        = $this->shopify_curl($url, $fulfillment);

            if ($data){
                return $data['fulfillment'];
            }

            return false;
        }

        public function get_variants($shopify_id)
        {
            $url  = $this->web_access . ".myshopify.com/admin/products/{$shopify_id}/variants.json";
            $data = $this->shopify_curl($url, [], 'GET');

            return $data;
        }

        public function update_variants($shopify_id, $arr_data)
        {
            $url = $this->web_access . ".myshopify.com/admin/products/{$shopify_id}.json";

            $variants = [
                "product" => [
                    'id'       => $shopify_id,
                    'variants' => $arr_data,
                ],
            ];

            $data = $this->shopify_curl($url, $variants, 'PUT');

            return $data;
        }

        /**
         * 上传商品
         *
         * @param $data
         *
         * @return bool
         * upload_goods
         * author: walker
         * Date: 2019/11/27
         * Time: 15:51
         * Note:
         */
        public function upload_goods($data)
        {
            $url = $this->web_access . ".myshopify.com/admin/products.json";

            $product = [
                'product' => [
                    'vendor'    => $this->web_name,
                    'title'     => $data['goods_name'],
                    'body_html' => $data['body_html'],
                    'tags'      => $data['tags'],
                    'variants'  => $data['variants'],
                    'images'    => $data['images'],
                    'options'   => $data['options'],
                    'published' => false,
                ],
            ];

            $data = $this->shopify_curl($url, $product);

            if ($data){
                return $data['product'];
            }

            return false;
        }

        /**
         * 上传商品sku
         *
         * @param       $product_id
         * @param       $image_id
         * @param array $res
         *
         * @return mixed
         * shopify_sku_img
         * author: walker
         * Date: 2019/11/27
         * Time: 15:51
         * Note:
         */
        public function shopify_sku_img($product_id, $image_id, $res = [])
        {
            $url  = $this->web_access . ".myshopify.com/admin/products/{$product_id}/images/{$image_id}.json";
            $data = $this->shopify_curl($url, $res, 'PUT');

            return $data;
        }

        /**
         * 发布的商品
         *
         * @param      $shopify_id
         * @param bool $is_show
         *
         * @return mixed
         * published_goods
         * author: walker
         * Date: 2019/11/27
         * Time: 15:52
         * Note:
         */
        public function published_goods($shopify_id, $is_show = false)
        {
            $url     = $this->web_access . ".myshopify.com/admin/products/{$shopify_id}.json";
            $product = [
                'product' => [
                    'id'        => $shopify_id,
                    'published' => $is_show,
                ],
            ];

            $data = $this->shopify_curl($url, $product, 'PUT');

            return $data;
        }

        /**
         * 删除商品
         *
         * @param $shopify_id
         *
         * @return mixed
         * goods_delete
         * author: walker
         * Date: 2019/11/27
         * Time: 15:52
         * Note:
         */
        public function goods_delete($shopify_id)
        {
            $url  = $this->web_access . ".myshopify.com/admin/products/{$shopify_id}.json";
            $data = $this->shopify_curl($url, '', 'DELETE');
            return $data;
        }

        /**
         * 上传的sku
         *
         * @param $shopify_id
         * @param $variants
         *
         * @return mixed
         * published_sku
         * author: walker
         * Date: 2019/11/27
         * Time: 15:52
         * Note:
         */
        public function published_sku($shopify_id, $variants)
        {
            $url = $this->web_access . ".myshopify.com/admin/products/{$shopify_id}.json";

            $var = [];
            foreach ($variants as $v) {
                $var[] = [
                    'option1'              => $v['option1'],
                    'option2'              => $v['option2'],
                    'sku'                  => $v['sku'],
                    'price'                => $v['price'],
                    'inventory_quantity'   => 9999,
                    "fulfillment_service"  => "manual",
                    'inventory_management' => 'shopify',
                ];
            }

            $product = [
                'product' => [
                    'id'       => $shopify_id,
                    'variants' => $var,
                ],
            ];

            $data = $this->shopify_curl($url, $product, 'PUT');

            return $data;
        }

        //更新sku 带数量
        public function published_up_sku($shopify_id, $variants)
        {
            $url     = $this->web_access . ".myshopify.com/admin/products/{$shopify_id}.json";
            $product = [
                'product' => [
                    'id'       => $shopify_id,
                    'variants' => $variants,
                ],
            ];

            $data = $this->shopify_curl($url, $product, 'PUT');

            return $data;
        }


        /**
         * 修改售卖状态
         *
         * @param $product_id
         * @param $val
         * published_status_sales
         * author: walker
         * Date: 2019/11/27
         * Time: 15:52
         * Note:
         */
        public function published_status_sales($product_id, $val)
        {
            $url = $this->web_access . ".myshopify.com/admin/products/{$product_id}.json";
            //$val 2 是false
            if ($val == 2){
                $product = ['product' => ['id' => $shopify_id, 'published' => false]];
            } else {
                $product = ['product' => ['id' => $shopify_id, 'published' => true]];
            }

            $data = $this->shopify_curl($url, $product, 'PUT');
        }

        public function update_up_goods($data, $is = '')
        {
            $url = $this->web_access . ".myshopify.com/admin/products/{$data['shopify_id']}.json";

            $var = [];
            foreach ($data['variants'] as $v) {
                $var[] = [
                    'option1'              => $v['option1'],
                    'option2'              => $v['option2'],
                    'sku'                  => $v['sku'],
                    'price'                => $v['price'],
                    'inventory_quantity'   => 9999,
                    "fulfillment_service"  => "manual",
                    'inventory_management' => 'shopify',
                ];
            }

            $product = [
                'product' => [
                    'title'     => $data['goods_name'],
                    'body_html' => $data['body_html'],
                    'tags'      => $data['tags'],
                    'variants'  => $var,
                    'images'    => $data['images'],
                    'published' => $data['is_sales'] ? true : false,
                ],
            ];

            //        if(!$is){
            //            $this->update_img($data['shopify_id'],$data['images'],$data['cunzai']);
            //        }

            $data = $this->shopify_curl($url, $product, 'PUT');

            if ($data){
                return $data['product'];
            }

            return false;
        }

        //修改图片
        public function update_img($shopify_id, $images, $cunzai)
        {
            //goods表shopify_id不存在就删图片
            if ($cunzai == 0){
                //获取这个商品在Shopify图片ID
                $url  = $this->web_access . ".myshopify.com/admin/products/{$shopify_id}/images.json";
                $data = $this->shopify_curl($url, null, 'GET');
                if ( !empty($data['images'])){
                    //删除所有图片
                    foreach ($data['images'] as $k => $v) {
                        $url = $this->web_access . ".myshopify.com/admin/products/{$shopify_id}/images/{$v['id']}.json";
                        $this->shopify_curl($url, [], 'DELETE');
                    }
                }

            }

            //上传图片
            foreach ($images as $k => $v) {
                $url            = $this->web_access . ".myshopify.com/admin/products/{$shopify_id}/images.json";
                $image['image'] = $v;
                $this->shopify_curl($url, $image, 'POST');
            }


        }


        public function shopify_user_add($created_at_min, $created_at_max)
        {

            $url = $this->web_access . '.myshopify.com/admin/api/2019-07/customers.json?limit=250&created_at_min=' . $created_at_min . '&created_at_max=' . $created_at_max;
            $arr = $this->shopify_curl($url, [], 'GET');
            return $arr;
        }

        /**
         * curl请求
         *
         * @param        $url
         * @param array  $post_data
         * @param string $method
         *
         * @return mixed
         * shopify_curl
         * author: walker
         * Date: 2019/11/27
         * Time: 15:53
         * Note:
         */
        public function shopify_curl($url, $post_data = [], $method = 'POST')
        {


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            if ($post_data || $method == 'DELETE'){
                $data_string = json_encode($post_data);

                $headers = [
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Content-Length:" . strlen($data_string),
                ];

                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $content = curl_exec($ch);

            curl_close($ch);

            return json_decode($content, true);
        }

    }
