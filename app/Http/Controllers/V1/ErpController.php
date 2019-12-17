<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Exports\LogisticsExport;
    use App\Http\Controllers\Controller;
    use App\Models\Erp\OrderInfo;
    use App\Models\Erp\SiteWeb;
    use App\Models\V1\OrderE;
    use Cassandra\Date;
    use http\Url;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use Maatwebsite\Excel\Facades\Excel;
    use Phpro\SoapClient\Exception\SoapException;
    use Phpro\SoapClient\Soap\HttpBinding\SoapRequest;

    class ErpController extends Controller
    {
        //
        protected static $ebDomain  = 'http://tuliang-eb.eccang.com';
        protected static $wmsDomain = 'http://tuliang.eccang.com';
        protected static $userName  = 'admin';
        protected static $userPwd   = 'QE4opraf7';

        public function getPlatformUser(Request $request)
        {
            $service = 'getPlatformUser';
            $params  = [];
            $result  = self::soapRequest($service, 'eb', $params);
            if ( !empty($result)){
                ajaxReturn(200, Code::$com[200], $result['data']);
            }
            ajaxReturn(4001, Code::$com[4001]);
        }

        /**
         * 获取物流费用列表
         *
         * @param Request $request
         * getShippingList
         * author: walker
         * Date: 2019/12/11
         * Time: 9:51
         * Note:
         */
        public function getShippingList(Request $request)
        {
            $request->validate([
                                   'start_time' => 'nullable|date',
                                   'end_time'   => 'nullable|date',
                                   //                                   'web_id'   => 'nullable|string|exists:connection.erp.siteweb',
                               ]);
            $page        = (int)$request->page ?: 1;
            $pageNum     = $request->pageNum ?: 10;
            $pageStart   = ($page - 1) * $pageNum;
            $webId       = $request->web_id;
            $orderStatus = $request->status;
            $where       = [];
            $startTime   = $request->start_time ? strtotime($request->start_time) : 0;
            $endTime     = $request->end_time ? strtotime($request->end) : time();
            if ( !empty($orderStatus)) $where['status'] = $orderStatus;
            $table = new OrderE();
            $table->whereBetween('createdDate', [\date('Y-m-d H:i:s', $startTime), \date('Y-m-d H:i:s', $endTime)]);
            $list          = $table->where($where)->offset($pageStart)->limit($pageNum)->get();
            $count         = $table->where($where)->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 获取E仓仓库列表
         *
         * @param Request $request
         * getWarehouse
         * author: walker
         * Date: 2019/12/9
         * Time: 16:34
         * Note:
         */
        public function getWarehouse(Request $request)
        {
            $service = 'getWarehouse';
            $params  = [];
            $result  = self::soapRequest($service, 'eb', $params);
            if ( !empty($result)){
                ajaxReturn(200, Code::$com[200], $result['data']);
            }
            ajaxReturn(4001, Code::$com[4001]);
        }

        /**
         * 获取E仓订单接口
         *
         * @param Request $request
         * getEOrders
         * author: walker
         * Date: 2019/12/9
         * Time: 16:39
         * Note:
         */
        public function getEOrders(Request $request)
        {
            $service = 'getOrders';
            $params  = [];

            $result = self::soapRequest($service, 'WMS', $params);
            if ( !empty($result)){
                ajaxReturn(200, Code::$com[200], $result['data']);
            }
            ajaxReturn(4001, Code::$com[4001]);
        }

        /**
         * 拉取订单
         *
         * @param Request $request
         *
         * @throws \SoapFault
         * pullEorders
         * author: walker
         * Date: 2019/12/10
         * Time: 15:21
         * Note:
         */
        public function pullEorders(Request $request)
        {


            $url         = $request->route()->getActionName();
            $sellerIdArr = [$request->seller_id_arr];
            $this->pullOrderList($url);
        }

        /**
         * 拉取订单存入数据库
         *
         * @param $url
         *
         * @throws \SoapFault
         * pullOrderList
         * author: walker
         * Date: 2019/12/10
         * Time: 15:21
         * Note:
         */
        private function pullOrderList($url)
        {
            $beginTime = time();

            $info = DB::table('pull_log')
                      ->where(['pull_url' => $url, 'status' => 1])
                      ->orderBy('add_time', 'desc')
                      ->first('current_page');

            $page = empty($info) ? 1 : $info->current_page + 1;

            $params               = [];
            $params['getDetail']  = 1;
            $params['getAddress'] = 1;
            $params['page']       = $page;
            $params['pageSize']   = 50;
            $params['getAddress'] = 1;

            $service = 'getOrderList';
            $result  = self::soapRequest($service, 'EB', $params);
            if ( !empty($result['data'])){
                //                $siteList              = $this->getSiteList();
                $orderTotalData        = [];
                $orderTotalGoodsData   = [];
                $orderTotalAddressData = [];
                $orderShip = [];
                foreach ($result['data'] as $key => $val) {
                    //订单
                    $referenceNo = (int)$val['saleOrderCode'];
                    $orderStatus = $val['status'];
                    $webId       = $this->getWebId($referenceNo) ?: 0;

                    $orderData                       = [];
                    $orderData['platform']           = $val['platform'];
                    $orderData['orderType']          = $val['orderType'];
                    $orderData['status']             = $orderStatus;
                    $orderData['processAgain']       = $val['processAgain'];
                    $orderData['refNo']              = $val['refNo'];
                    $orderData['saleOrderCode']      = $val['saleOrderCode'];
                    $orderData['warehouseOrderCode'] = $val['warehouseOrderCode'];
                    $orderData['companyCode']        = $val['companyCode'];
                    $orderData['userAccount']        = $val['userAccount'];
                    //                    $orderData['platformUserName']       = $val['platformUserName'];
                    $orderData['shippingMethod']         = $val['shippingMethod'];
                    $orderData['shippingMethodNo']       = $val['shippingMethodNo'];
                    $orderData['shippingMethodPlatform'] = $val['shippingMethodPlatform'];
                    $orderData['warehouseId']            = $val['warehouseId'];
                    $orderData['warehouseCode']          = $val['warehouseCode'];
                    $orderData['createdDate']            = $val['createdDate'];
                    $orderData['updateDate']             = $val['updateDate'];
                    $orderData['datePaidPlatform']       = strtotime($val['datePaidPlatform']) > 0 ? strtotime($val['datePaidPlatform']) : 0;
                    $orderData['platformShipStatus']     = $val['platformShipStatus'] ?: null;
                    $orderData['platformShipTime']       = strtotime($val['platformShipTime']) > 0 ? strtotime($val['platformShipTime']) : 0;
                    $orderData['dateWarehouseShipping']  = strtotime($val['dateWarehouseShipping']) > 0 ? strtotime($val['dateWarehouseShipping']) : 0;

                    $orderData['dateLatestShip']     = strtotime($val['dateLatestShip']);
                    $orderData['currency']           = $val['currency'];
                    $orderData['amountpaid']         = $val['amountpaid'];
                    $orderData['subtotal']           = $val['subtotal'];
                    $orderData['shipFee']            = $val['shipFee'];
                    $orderData['platformFeeTotal']   = $val['platformFeeTotal'];
                    $orderData['finalvaluefeeTotal'] = $val['finalvaluefeeTotal'];
                    $orderData['otherFee']           = $val['otherFee'];
                    //                    $orderData['costShipFee']            = $val['costShipFee'];
                    $orderData['buyerId']             = $val['buyerId'];
                    $orderData['buyerName']           = $val['buyerName'];
                    $orderData['buyerMail']           = $val['buyerMail'];
                    $orderData['site']                = $val['site'];
                    $orderData['countryCode']         = $val['countryCode'];
                    $orderData['productCount']        = $val['productCount'];
                    $orderData['orderWeight']         = $val['orderWeight'];
                    $orderData['orderDesc']           = $val['orderDesc'];
                    $orderData['paypalTransactionId'] = $val['paypalTransactionId'];
                    $orderData['abnormalType']        = $val['abnormalType'];
                    $orderData['abnormalReason']      = $val['abnormalReason'];
                    //                    $orderData['orderConfigDatas']       = $val['orderConfigDatas'];
                    $orderData['addTime'] = time();
                    $orderData['webId']   = $webId;

                    $orderData['addTime'] = time();
                    $orderTotalData[]     = $orderData;
                    if(!empty($val['platformShipStatus'])){
                        $ship = [];
                        $ship['warehouseOrderCode'] = $val['warehouseOrderCode'];
                        $ship['saleOrderCode'] = $val['saleOrderCode'];
                        $ship['shippingMethodNo'] = $val['shippingMethodNo'];
                        $ship['orderWeight'] = $val['orderWeight'];
                        $ship['shippingMethod'] = $val['shippingMethod'];
                        $ship['platformFeeTotal'] = $val['platformFeeTotal'];
                        $ship['shipFee'] = $val['shipFee'];
                        $ship['dateWarehouseShipping'] = strtotime($val['dateWarehouseShipping']);
                        $ship['addTime'] = \date('Y-m-d H:i:s');
                        $ship['webId'] = $webId;
                        $ship['totalFee'] = round($val['platformFeeTotal'],3)+round($val['shipFee'],3);
                        $orderShip[] = $ship;
                    }

                    //订单商品
                    if ( !empty($val['orderDetails'])){
                        foreach ($val['orderDetails'] as $key1 => $val1) {
                            $orderGoodsData               = [];
                            $orderGoodsData['productSku'] = $val1['productSku'];
                            //                            $orderGoodsData['sku']               = $val1['sku'];
                            $orderGoodsData['unitPrice']         = round($val1['unitPrice'], 3);
                            $orderGoodsData['qty']               = $val1['qty'];
                            $orderGoodsData['productTitle']      = $val1['productTitle'];
                            $orderGoodsData['pic']               = $val1['pic'];
                            $orderGoodsData['opSite']            = $val1['opSite'];
                            $orderGoodsData['productUrl']        = $val1['productUrl'];
                            $orderGoodsData['refItemId']         = $val1['refItemId'];
                            $orderGoodsData['opRefItemLocation'] = $val1['opRefItemLocation'];
                            $orderGoodsData['unitFinalValueFee'] = round($val1['unitFinalValueFee'], 3);
                            $orderGoodsData['transactionPrice']  = round($val1['transactionPrice'], 3);
                            $orderGoodsData['operTime']          = $val1['operTime'];

                            $orderGoodsData['orderStatus']   = $orderStatus;
                            $orderGoodsData['saleOrderCode'] = $referenceNo;
                            $orderGoodsData['addTime']       = time();
                            $orderGoodsData['webId']         = $webId;
                            $orderTotalGoodsData[]           = $orderGoodsData;
                        }
                    }
                    //订单地址
                    if ( !empty($val['orderAddress'])){
                        $orderAddress                = [];
                        $orderAddress['name']        = $val['orderAddress']['name'];
                        $orderAddress['companyName'] = $val['orderAddress']['companyName'];
                        $orderAddress['line1']       = $val['orderAddress']['line1'];
                        $orderAddress['line2']       = $val['orderAddress']['line2'];
                        $orderAddress['line3']       = $val['orderAddress']['line3'];
                        $orderAddress['district']    = $val['orderAddress']['district'];
                        $orderAddress['cityName']    = $val['orderAddress']['cityName'];
                        $orderAddress['postalCode']  = $val['orderAddress']['postalCode'];
                        $orderAddress['phone']       = $val['orderAddress']['phone'];
                        $orderAddress['state']       = $val['orderAddress']['state'];
                        $orderAddress['countryCode'] = $val['orderAddress']['countryCode'];
                        $orderAddress['countryName'] = $val['orderAddress']['countryName'];
                        $orderAddress['doorplate']   = $val['orderAddress']['doorplate'];
                        $orderAddress['createdDate'] = $val['orderAddress']['createdDate'];
                        $orderAddress['updateDate']  = $val['orderAddress']['updateDate'];

                        $orderAddress['orderStatus']   = $orderStatus;
                        $orderAddress['saleOrderCode'] = $referenceNo;
                        $orderAddress['addTime']       = time();
                        $orderAddress['webId']         = $webId;
                        $orderTotalAddressData[]       = $orderAddress;
                    }
                }
                $pullLog                 = [];
                $pullLog['pull_url']     = $url;
                $pullLog['pull_time']    = \date('Y/m/d H:i:s');
                $pullLog['count']        = $result['totalCount'];
                $pullLog['page_size']    = $result['pageSize'];
                $pullLog['current_page'] = $page;
                $pullLog['status']       = 1;
                $pullLog['add_time']     = time();
                $pullData                = DB::table('pull_log')
                                             ->where(['pull_url' => $url, 'current_page' => $page])
                                             ->first('id');
                if (empty($pullData)){
                    DB::beginTransaction();
                    try {
                        DB::beginTransaction();
                        DB::table('e_orders')->insert($orderTotalData);
                        DB::table('ship')->insert($orderShip);
                        DB::rollBack();
                        DB::table('e_order_goods')->insert($orderTotalGoodsData);
                        DB::rollBack();
                        DB::table('e_address')->insert($orderTotalAddressData);
                        DB::rollBack();
                        $pullLog['spend_time'] = time() - $beginTime;
                        DB::table('pull_log')->insert($pullLog);
                        DB::rollBack();
                        DB::commit();
                        $endTime = time();
                        ajaxReturn(200, 'success', ['spend_time' => $endTime - $beginTime]);

                    } catch (\Exception $e) {
                        $pullLog['status']  = 0;
                        $pullLog['err_msg'] = $e->getMessage();
                        DB::table('pull_log')->insert($pullLog);
                        ajaxReturn(4002, 'error', $e->getMessage());
                    }
                };
            } else {
                ajaxReturn(4001, 'not find data');
            }

        }

        /**
         * 获取网站列表
         *
         * @return mixed
         * getSiteList
         * author: walker
         * Date: 2019/12/10
         * Time: 9:51
         * Note:
         */
        private function getSiteList()
        {
            $siteList = Cache::get('site_web');

            if (empty($siteList)){
                $model   = new SiteWeb();
                $listRow = $model->where(['type' => 1])
                                 ->selectRaw('web_id,web_name')
                                 ->get();
                $listRow = toArr($listRow);
                foreach ($listRow as $key => $val) {
                    $siteList[$val['web_name']] = $val['web_id'];
                }
                Cache::add('site_web', $siteList);
            }
            return $siteList;

        }

        /**
         * 获取webid
         *
         * @param $referenceNo
         *
         * @return mixed
         * getWebId
         * author: walker
         * Date: 2019/12/10
         * Time: 10:42
         * Note:
         */
        private function getWebId($referenceNo)
        {
            $model = new OrderInfo();
            $info  = $model->where(['source_id' => $referenceNo])
                           ->first('web_id');
            if ( !empty($info)){
                return $info->web_id;
            }
            return false;
        }

        /**
         * 获取物流列表
         *
         * @param Request $request
         * getLogisticsList
         * author: walker
         * Date: 2019/12/16
         * Time: 14:47
         * Note:
         */
        public function getLogisticsList(Request $request)
        {
            $request->validate([
                                   'start_time' => 'nullable|date',
                                   'end_time'   => 'nullable|date',
                               ]);
            $page      = (int)$request->page ?: 1;
            $pageNum   = $request->pageNum ?: 10;
            $pageStart = ($page - 1) * $pageNum;
            $webId     = $request->web_id;
            $where     = [];
            $startTime = $request->start_time ? strtotime($request->start_time) : 0;
            $endTime   = $request->end_time ? strtotime($request->end) : time();
            if ( !empty($webId)) $where['web_id'] = $webId;
            $table = DB::table('ship');
            $table->whereBetween('dateWarehouseShipping', [$startTime, $endTime]);
//            $field                       = "id,webId,warehouseCode,shippingMethodNo,orderWeight,shippingMethod,platformFeeTotal,shipFee,dateWarehouseShipping";
            $list                        = $table
                ->where($where)
                ->offset($pageStart)
//                ->selectRaw($field)
                ->orderBy('dateWarehouseShipping', 'desc')
                ->limit($pageNum)
                ->get();
            $count                       = $table->where($where)->count();
            $list                        = toArr($list);
//            foreach ($list as $key => $val) {
//                $list[$key]['total_fee'] = $val['platformFeeTotal'] + $val['shipFee'];
//            }
            if (!empty($request->download)){
                return Excel::download(new LogisticsExport(toArr($list)), 'test.xlsx');
            };
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 获取物流费用图表
         * @param Request $request
         * getLogisticsLineChart
         * author: walker
         * Date: 2019/12/16
         * Time: 16:21
         * Note:
         */
        public function getLogisticsLineChart(Request $request)
        {

            $where                       = [];
            $where['platformShipStatus'] = 1;
            if(!empty($request->web_id))$where['webId'] = $request->web_id;

            $orderList                   = DB::table('ship')
                                             ->where($where)
                                             ->orderBy('dateWarehouseShipping', 'desc')
                                             ->selectRaw('shipFee,dateWarehouseShipping,webId')
                                             ->get();
            $orderList                   = toArr($orderList);
            #在进行图表统计的时候直接从数据库取得的数据有的月份可能是没有的,不过月份比较少可直接写死,同样也需要补全
            $year
                = date('Y', time());
            #一年的月份
            $month = [
                0  => $year . '-01',
                1  => $year . '-02',
                2  => $year . '-03',
                3  => $year . '-04',
                4  => $year . '-05',
                5  => $year . '-06',
                6  => $year . '-07',
                7  => $year . '-08',
                8  => $year . '-09',
                9  => $year . '-10',
                10 => $year . '-11',
                11 => $year . '-12',
            ];
            foreach ($month as $key => $val) {
                $data[$key] = [
                    'date'  => $val,
                    'value' => 0,
                ];
                foreach ($orderList as $key1 => $val1) {
                    if($val==\date('Y-m',$val1['dateWarehouseShipping'])){
                        $data[$key]['value']=round($data[$key]['value'],2)+round($val1['shipFee'],2);
                    };
                }
            }
            ajaxReturn(200,Code::$com[200],$data);
        }

        /**
         * Erp SOAP请求封装
         *
         * @param       $service
         * @param       $systemCode
         * @param array $params
         *
         * @return array|mixed
         * @throws \SoapFault
         * soapRequest
         * author: walker
         * Date: 2019/11/23
         * Time: 10:49
         * Note:
         */
        private static function soapRequest($service, $systemCode, $params = [])
        {
            $method     = 'callService';
            $data       = [
                'ask'     => 'Failure',
                'message' => 'SoapRequest Error',
                'data'    => [],
            ];
            $systemCode = strtoupper($systemCode);//code转成大写
            /**
             * 校验编码
             */
            if ( !in_array($systemCode, ['EB', 'WMS'])){
                $data['message'] = 'system code not find';
                return $data;
            };
            //设置域名
            $domian = $systemCode == 'EB' ? self::$ebDomain : self::$wmsDomain;
            if (empty($domian)){
                $data['message'] = 'domain not empty';
                return $data;
            }
            $domian = trim($domian, '/');//去除斜线
            $wsdl   = $domian . '/default/svc-open/web-service-v2';

            $requestData = [
                'userName' => self::$userName,
                'userPass' => self::$userPwd,
                'service'  => $service,
            ];

            if ( !empty($params)) $requestData['paramsJson'] = json_encode($params);
            try {
                $option   = [
                    'trace' => true,//调试信息
                ];
                $client   = new \SoapClient($wsdl, $option);
                $res      = $client->__soapCall($method, ['parameters' => $requestData]);
                $response = $res->response;
                $data     = json_decode($response, true);

            } catch (SoapException $e) {
                $data['message'] = $e->getMessage();
            }
            return $data;
        }
    }
