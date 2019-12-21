<?php

    namespace App\Models\Erp;

    use App\Http\Controllers\V1\SystemController;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\DB;
    use SebastianBergmann\CodeCoverage\Report\PHP;
    use function GuzzleHttp\Psr7\str;

    class ShopifyApi extends Model
    {
        //
        private static $limit    = 50;
        private static $timeDiff = 43200;//中美时差


        /**
         * 统计需要拉取的订单
         *
         * @param $webAccess
         * @param $startTime
         * @param $endTime
         * countProducts
         * author: walker
         * Date: 2019/12/5
         * Time: 11:55
         * Note:
         */
        public function countData($downTime = '', $type = 0)
        {
            $webModel = new SiteWeb();
            $webLists = $webModel->where(['type' => 1, 'is_delete' => 0])->get()->toArray();
            $webModel = new SiteWeb();
            foreach ($webLists as $k => $v) {
                $t     = time();
                $info  = $v;
                $webId = $v['web_id'];
                //            $info = $webModel
                //                ->where(['web_id' => $webId])
                //                ->first();
                //            if (empty($info)) return false;
                //            $info    = toArr($info);
                $pullLog = DB::table('shopify_pull_info')
                             ->where(['web_id' => $webId, 'type' => 0])
                             ->orderBy('end_time', 'desc')
                             ->first();
                $pullLog = toArr($pullLog);
                if ( !empty($pullLog)){
                    $start = strtotime($pullLog['end_time']);
                    $end   = strtotime(date('Y-m-d H:i')) - self::$timeDiff;
                } else {
                    $today       = strtotime('today') - self::$timeDiff;
                    $defaultTime = date('Y-m-d ', strtotime('2018-3-10'));
                    $start       = strtotime($defaultTime) - self::$timeDiff;
                    $end         = $today;
                }
                $limit   = 100;
                $timeStr = $this->getTimeString($start, $end);
                if ( !empty($timeStr)){
                    switch ($type) {
                        case 0:
                            $url     = $info['web_access'] . '.myshopify.com/admin/orders/count.json?' . $timeStr . '&status=any';
                            $pullUrl = $info['web_access'] . '.myshopify.com/admin/orders.json?' . $timeStr . "&page=%s" . '&limit=' . $limit . '&status=any';
                            break;
                        case 1:
                            $url     = $info['web_access'] . '.myshopify.com/admin/customers/count.json?' . $timeStr . '&status=any';
                            $pullUrl = $info['web_access'] . '.myshopify.com/admin/customers.json?' . $timeStr . '&page=%s' . '&limit=' . $limit . '&status=any';
                            break;
                    }
                    $shopify_pull_info_id = DB::table('shopify_pull_info')
                                              ->where(['pull_url' => $url])
                                              ->first('id');
                    if ( !empty($shopify_pull_info_id)){
                        continue;
                    }
                    try {
                        if (empty($shopify_pull_info_id)){
                            $countInfo = json_decode(file_get_contents($url), true);
                            //                            fp($countInfo);
                            if (isset($countInfo)){
                                $count                      = $countInfo['count'];
                                $totalPage                  = ceil($count / $limit);
                                $pullInfoData               = [];
                                $pullInfoData['web_id']     = $webId;
                                $pullInfoData['type']       = $type;
                                $pullInfoData['pull_times'] = 0;
                                $pullInfoData['web_access'] = $info['web_access'];
                                $pullInfoData['total_page'] = $totalPage;
                                $pullInfoData['page_size']  = $limit;
                                $pullInfoData['count_num']  = $count;
                                $pullInfoData['time_str']   = $timeStr;
                                $pullInfoData['pull_url']   = $url;
                                $pullInfoData['start_time'] = date('Y-m-d H:i:s', $start);
                                $pullInfoData['end_time']   = date('Y-m-d H:i:s', $end);
                                $pullInfoData['web_id']     = $webId;
                                $pullInfoData['add_time']   = time();
                                $pullLogData                = [];
                                if ($count > 0){
                                    for ($i = 1; $i <= $totalPage; $i++) {
                                        $pullLog = [];
                                        //                                        print_r($pullUrl.PHP_EOL);

                                        $pullLogUrl              = sprintf($pullUrl, $i);
                                        $pullLog['current_page'] = $i;
                                        $pullLog['web_name']     = $info['web_name'];
                                        $pullLog['pull_url']     = $pullLogUrl;
                                        $pullLog['pull_status']  = 0;
                                        $pullLog['web_id']       = $webId;
                                        $pullLog['type']         = $type;
                                        $pullLog['add_time']     = time();
                                        $pullLogData []          = $pullLog;
                                    }
                                };
                                DB::beginTransaction();
                                try {
                                    DB::beginTransaction();
                                    $pullInfoData['spend_time'] = time() - $t;
                                    $pullInfoId                 = DB::table('shopify_pull_info')->insertGetId($pullInfoData);
                                    DB::rollBack();
                                    foreach ($pullLogData as $key => $val) {
                                        $pullLogData[$key]['pull_info_id'] = $pullInfoId;
                                    }
                                    if ($count > 0){
                                        DB::table('shopify_pull_log')->insert($pullLogData);
                                    };
                                    DB::commit();
                                } catch (\Exception $exception) {
                                    DB::rollBack();
                                    $pullInfo = DB::table('shopify_pull_info')->where(['web_id' => $webId, 'pull_url' => $url])->first('id');
                                    if ( !empty($pullInfo)){
                                        $pullLogData['err_msg'] = $exception->getMessage();
                                        DB::table('shopify_pull_info')->insert($pullLogData);
                                    } else {
                                        DB::table('shopify_pull_info')->where(['web_id' => $webId, 'pull_url' => $url])->update(['err_msg' => $exception->getMessage()]);
                                    }
                                    ajaxReturn(4001, 'error', $exception->getMessage());
                                }
                            }
                        } else {
                            continue;
                        }
                    } catch (\Exception $exception) {
                        $pullInfoData                = [];
                        $pullInfoData['web_id']      = $webId;
                        $pullInfoData['type']        = $type;
                        $pullInfoData['pull_times']  = 0;
                        $pullInfoData['web_access']  = $info['web_access'];
                        $pullInfoData['total_page']  = 0;
                        $pullInfoData['page_size']   = $limit;
                        $pullInfoData['count_num']   = 0;
                        $pullInfoData['time_str']    = $timeStr;
                        $pullInfoData['pull_url']    = $url;
                        $pullInfoData['start_time']  = date('Y-m-d H:i:s', $start);
                        $pullInfoData['end_time']    = date('Y-m-d H:i:s', $end);
                        $pullInfoData['web_id']      = $webId;
                        $pullInfoData['add_time']    = time();
                        $pullInfoData['pull_status'] = 3;
                        $pullInfoData['spend_time']  = time() - $t;
                        $pullInfoData['err_msg']     = $exception->getMessage();
                        $pullInfoId                  = DB::table('shopify_pull_info')->insertGetId($pullInfoData);
                        continue;
                    }


                }
            }


        }

        public function countOrder($start, $end)
        {
            $timeStr = $this->getTimeString($start, $end);
            $url     = 'https://cb8bbdfb793b108dd24ff15aa3681275:1c198bbc47771743156c35b83ae73a5c@712styles' . '.myshopify.com/admin/orders/count.json?' . $timeStr . '&status=any';
            return json_decode(file_get_contents($url), true);
        }

        /**
         * 抓取订单信息并保存到数据库
         *
         * @param $webAccess
         * getOrderData
         * author: walker
         * Date: 2019/12/6
         * Time: 9:25
         * Note:
         */
        public function pullOrderData()
        {

            $beginTime = time();

            //获取最近拉取的page
            $pullLog = DB::table('shopify_pull_log')
                         ->where([
                                     'pull_status' => 0,
                                     'type'        => 0,
                                 ])
                         ->orderBy('id', 'asc')
                         ->orderBy('current_page', 'asc')
                         ->first();

            $pullLog = toArr($pullLog);
            if ( !empty($pullLog)){
                $url   = $pullLog['pull_url'];
                $webId = $pullLog['web_id'];
                $res   = $this->shopifyCurl($url);

                if ( !empty($res['orders'])){
                    $shopifyOrderData       = [];
                    $shopifyOrderLineItem   = [];
                    $shopifyOrderClient     = [];
                    $shopifyCustomer        = [];
                    $shopifyCustomerAddress = [];
                    $repeatOrder            = [];

                    foreach ($res['orders'] as $key => $val) {
                        $orderId                  = $val['id'] ?: 0;
                        $createTime               = strtotime($val['created_at']);
                        $insertData               = [];
                        $insertData['email']      = $val['email'];
                        $insertData['shopify_id'] = $orderId;
                        $insertData['created_at'] = strtotime($val['created_at']);
                        $insertData['updated_at'] = strtotime($val['updated_at']);

                        $insertData['create_time'] = date('Y-m-d H:i:s', strtotime($val['created_at']));
                        $insertData['update_time'] = date('Y-m-d H:i:s', strtotime($val['updated_at']));

                        $insertData['number']                  = $val['number'];
                        $insertData['note']                    = $val['note'];
                        $insertData['token']                   = $val['token'];
                        $insertData['gateway']                 = $val['gateway'];
                        $insertData['total_price']             = round($val['total_price'], 2);
                        $insertData['subtotal_price']          = round($val['subtotal_price'], 2);
                        $insertData['total_tax']               = round($val['total_tax'], 2);
                        $insertData['total_discounts']         = round($val['total_discounts'], 2);
                        $insertData['total_line_items_price']  = round($val['total_line_items_price'], 2);
                        $insertData['total_price_usd']         = round($val['total_price_usd'], 2);
                        $insertData['total_tip_received']      = round($val['total_tip_received'], 2);
                        $insertData['total_weight']            = $val['total_weight'];
                        $insertData['currency']                = $val['currency'];
                        $insertData['financial_status']        = $val['financial_status'];
                        $insertData['confirmed']               = $val['confirmed'];
                        $insertData['buyer_accepts_marketing'] = $val['buyer_accepts_marketing'];
                        $insertData['name']                    = $val['name'];
                        $insertData['referring_site']          = $val['referring_site'];
                        $insertData['cancelled_at']            = strtotime($val['cancelled_at']);
                        $insertData['cancel_reason']           = $val['cancel_reason'];
                        $insertData['checkout_token']          = $val['checkout_token'];
                        $insertData['reference']               = $val['reference'];
                        $insertData['source_url']              = $val['source_url'];
                        $insertData['processed_at']            = strtotime($val['processed_at']);
                        $insertData['customer_locale']         = $val['customer_locale'];
                        $insertData['app_id']                  = $val['app_id'];
                        $insertData['browser_ip']              = $val['browser_ip'];
                        $insertData['order_number']            = (int)$val['order_number'];
                        $insertData['checkout_id']             = $val['checkout_id'];
                        $insertData['source_name']             = $val['source_name'];
                        $insertData['tags']                    = $val['tags'];
                        $insertData['contact_email']           = $val['contact_email'];
                        $insertData['order_status_url']        = $val['order_status_url'];
                        $insertData['presentment_currency']    = $val['presentment_currency'];
                        $insertData['user_name']               = $val['billing_address']['name'];
                        $insertData['first_name']              = $val['billing_address']['first_name'];
                        $insertData['last_name']               = $val['billing_address']['last_name'];
                        $insertData['address1']                = $val['billing_address']['address1'];
                        $insertData['address2']                = $val['billing_address']['address2'];
                        $insertData['company']                 = $val['billing_address']['company'];
                        $insertData['phone']                   = $val['billing_address']['phone'];
                        $insertData['city']                    = $val['billing_address']['city'];
                        $insertData['zip']                     = $val['billing_address']['zip'];
                        $insertData['province_code']           = $val['billing_address']['province_code'];
                        $insertData['country_code']            = $val['billing_address']['country_code'];
                        $insertData['province']                = $val['billing_address']['province'];
                        $insertData['country']                 = $val['billing_address']['country'];
                        $insertData['latitude']                = $val['billing_address']['latitude'];
                        $insertData['longitude']               = $val['billing_address']['longitude'];

                        $insertData['web_id']   = $webId;
                        $insertData['add_time'] = date('Y-h-d H:i:s');
                        $shopifyOrderData[]     = $insertData;
                        //订单商品
                        if ( !empty($val['line_items'])){

                            foreach ($val['line_items'] as $key1 => $val1) {
                                $goodsItem                                 = [];
                                $goodsItem['order_goods_id']               = $val1['id'];
                                $goodsItem['variant_id']                   = $val1['variant_id'];
                                $goodsItem['title']                        = $val1['title'];
                                $goodsItem['quantity']                     = $val1['quantity'];
                                $goodsItem['sku']                          = $val1['sku'];
                                $goodsItem['variant_title']                = $val1['variant_title'];
                                $goodsItem['vendor']                       = $val1['vendor'];
                                $goodsItem['fulfillment_service']          = $val1['fulfillment_service'];
                                $goodsItem['product_id']                   = $val1['product_id'];
                                $goodsItem['requires_shipping']            = $val1['requires_shipping'];
                                $goodsItem['taxable']                      = $val1['taxable'];
                                $goodsItem['gift_card']                    = $val1['gift_card'];
                                $goodsItem['name']                         = $val1['name'];
                                $goodsItem['variant_inventory_management'] = $val1['variant_inventory_management'];
                                $goodsItem['product_exists']               = $val1['product_exists'];
                                $goodsItem['fulfillable_quantity']         = $val1['fulfillable_quantity'];
                                $goodsItem['grams']                        = $val1['grams'];
                                $goodsItem['price']                        = round($val1['price'], 2);
                                $goodsItem['total_discount']               = round($val1['total_discount'], 2);
                                $goodsItem['price_set']                    = json_encode($val1['price_set'], true);
                                $goodsItem['total_discount_set']           = json_encode($val1['total_discount_set'], true);
                                $goodsItem['origin_location']              = json_encode($val1['origin_location'], true);
                                $goodsItem['web_id']                       = $webId;
                                $goodsItem['order_id']                     = $orderId;
                                $shopifyOrderLineItem[]                    = $goodsItem;
                            }
                        }
                        //订单来源客户端
                        if ( !empty($val['client_details'])){
                            $client                    = [];
                            $client['shopify_id']      = $orderId;
                            $client['browser_ip']      = $val['client_details']['browser_ip'];
                            $client['accept_language'] = $val['client_details']['accept_language'];
                            $client['user_agent']      = $val['client_details']['user_agent'];
                            $client['session_hash']    = $val['client_details']['session_hash'];
                            $client['browser_width']   = $val['client_details']['browser_width'];
                            $client['browser_height']  = $val['client_details']['browser_height'];
                            $client['web_id']          = $webId;
                            $client['created_at']      = $createTime;
                            $client['add_time']        = date('Y-h-d H:i:s');
                            $shopifyOrderClient[]      = $client;
                        }
                        //订单顾客
                        if ( !empty($val['customer'])){

                            $customer                                 = [];
                            $customer['customer_id']                  = $val['customer']['id'] ?: 0;
                            $customer['email']                        = $val['customer']['email'];
                            $customer['accepts_marketing']            = $val['customer']['accepts_marketing'];
                            $customer['created_at']                   = strtotime($val['customer']['created_at']);
                            $customer['updated_at']                   = strtotime($val['customer']['updated_at']);
                            $customer['first_name']                   = $val['customer']['first_name'];
                            $customer['last_name']                    = $val['customer']['last_name'];
                            $customer['orders_count']                 = $val['customer']['orders_count'];
                            $customer['state']                        = $val['customer']['state'];
                            $customer['total_spent']                  = $val['customer']['total_spent'];
                            $customer['last_order_id']                = $val['customer']['last_order_id'];
                            $customer['note']                         = $val['customer']['note'];
                            $customer['verified_email']               = $val['customer']['verified_email'];
                            $customer['multipass_identifier']         = $val['customer']['multipass_identifier'];
                            $customer['tax_exempt']                   = $val['customer']['tax_exempt'];
                            $customer['phone']                        = $val['customer']['phone'];
                            $customer['tags']                         = $val['customer']['tags'];
                            $customer['last_order_name']              = $val['customer']['last_order_name'];
                            $customer['currency']                     = $val['customer']['currency'];
                            $customer['accepts_marketing_updated_at'] = strtotime($val['customer']['accepts_marketing_updated_at']);
                            $customer['marketing_opt_in_level']       = $val['customer']['marketing_opt_in_level'];
                            $customer['admin_graphql_api_id']         = $val['customer']['admin_graphql_api_id'];
                            $customer['web_id']                       = $webId;
                            $customer['add_time']                     = date('Y-m-d H:i:s');
                            $shopifyCustomer[]                        = $customer;


                        }
                        //用户地址
                        if ( !empty($val['customer']['default_address'])){
                            $address                  = [];
                            $address['customer_id']   = $val['customer']['default_address']['customer_id'] ?: 0;
                            $address['address_id']    = $val['customer']['default_address']['id'] ?: 0;
                            $address['first_name']    = $val['customer']['default_address']['first_name'];
                            $address['last_name']     = $val['customer']['default_address']['last_name'];
                            $address['company']       = $val['customer']['default_address']['company'];
                            $address['address1']      = $val['customer']['default_address']['address1'];
                            $address['address2']      = $val['customer']['default_address']['address2'];
                            $address['city']          = $val['customer']['default_address']['city'];
                            $address['province']      = $val['customer']['default_address']['province'];
                            $address['country']       = $val['customer']['default_address']['country'];
                            $address['zip']           = $val['customer']['default_address']['zip'];
                            $address['phone']         = $val['customer']['default_address']['phone'];
                            $address['name']          = $val['customer']['default_address']['name'];
                            $address['province_code'] = $val['customer']['default_address']['province_code'];
                            $address['country_code']  = $val['customer']['default_address']['country_code'];
                            $address['country_name']  = $val['customer']['default_address']['country_name'];
                            $address['default']       = $val['customer']['default_address']['default'];
                            $address['web_id']        = $webId;
                            $shopifyCustomerAddress[] = $address;
                        }
                    }

                    $pullLogData                 = [];
                    $pullLogData['pull_time']    = date('Y-m-d H:i:s');
                    $pullLogData['pull_status']  = 1;
                    $pullLogData['spend_time']   = time() - $beginTime;
                    $pullLogData['update_time']  = date('Y-m-d H:i:s');
                    $pullLogData['repeat_order'] = json_encode($repeatOrder, true);
                    DB::beginTransaction();
                    try {
                        DB::beginTransaction();
                        //shopify订单数据
                        DB::table('shopify_order')->insert($shopifyOrderData);
                        //shopify订单商品数据
                        DB::table('shopify_order_line_item')->insert($shopifyOrderLineItem);
                        //shopify顾客信息
                        DB::table('shopify_order_customer')->insert($shopifyCustomer);
                        //shopify地址信息
                        DB::table('shopify_address')->insert($shopifyCustomerAddress);
                        //shopify客户端信息
                        DB::table('shopify_order_client')->insert($shopifyOrderClient);
                        $pullLog['spend_time'] = time() - $beginTime;
                        DB::table('shopify_pull_log')->where(['id' => $pullLog['id']])->update($pullLogData);
                        DB::commit();
                        ajaxReturn(200,'成功');

                    } catch (\Exception $exception) {

                        DB::rollBack();
                        $pullLog['spend_time']              = time() - $beginTime;
                        $addData                            = [];
                        $addData['shopify_order']           = $shopifyOrderData;
                        $addData['shopify_order_line_item'] = $shopifyOrderLineItem;
                        $addData['shopify_customer']        = $shopifyCustomer;
                        $addData['shopify_address']         = $shopifyCustomerAddress;
                        $addData['shopify_order_client']    = $shopifyOrderClient;
                        $pullLogData['pull_status']         = 2;
                        $pullLogData['err_msg']             = $exception->getMessage();
                        $pullLogData['insert_data']         = json_encode($addData, true);
                        DB::table('shopify_pull_log')->where(['id' => $pullLog['id']])->update($pullLogData);
                        ajaxReturn(4003,'error',$exception->getMessage());
                        //                        DB::table('shopify_pull_log')
                        //                          ->where(['id' => $pullLog['id']])
                        //                          ->update($pullLogData);
                    }

                    //修改订单状态
                }else{
                    ajaxReturn(4003,'订单没有数据',$res);
                }
            }else{
                ajaxReturn(4004,'未查到该日志',$pullLog);
            }

        }

        public function getReport()
        {
            $webAccess = 'https://cb8bbdfb793b108dd24ff15aa3681275:1c198bbc47771743156c35b83ae73a5c@712styles';
            $url       = $webAccess . '.myshopify.com/admin/orders.json?shopify_ql=SHOW sales BY country FROM order SINCE -1m UNTIL today ORDER BY sales';
            $res       = $this->shopifyCurl($url);
            fp($res);
        }


        /**
         * 获取组装时间字符串
         *
         * @param $startTime
         * @param $endTime
         *
         * @return string
         * getTimeString
         * author: walker
         * Date: 2019/12/5
         * Time: 13:31
         * Note:
         */
        private function getTimeString($startTime, $endTime)
        {
            if ($startTime > $endTime) return false;
            if ($endTime - $startTime > 60 * 60){
                $start = date('Y-m-d\TH:i:s-04:00', $startTime);
                $end   = date('Y-m-d\TH:i:s-04:00', $endTime);
                return 'updated_at_min=' . $start . '&updated_at_max=' . $end;
            } else {
                return false;
            }

        }

        /**
         * 获取接口返回数据
         *
         * @param $url
         *
         * @return mixed
         * getApiRes
         * author: walker
         * Date: 2019/12/5
         * Time: 13:33
         * Note:
         */
        private function getApiRes($url)
        {
            if (empty($url)) return false;
            try {
                $res = file_get_contents($url);
                return json_decode($res, true);
            } catch (\Exception $e) {

                //                ajaxReturn(4001, $e->getMessage());
            }

        }


        /**
         * shopify curl
         *
         * @param        $url
         * @param array  $post_data
         * @param string $method
         *
         * @return mixed
         * shopifyCurl
         * author: walker
         * Date: 2019/12/5
         * Time: 11:55
         * Note:
         */
        private function shopifyCurl($url, $post_data = [], $method = 'POST')
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
