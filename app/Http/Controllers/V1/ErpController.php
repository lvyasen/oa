<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Exports\LogisticsExport;
    use App\Http\Controllers\Controller;
    use App\Models\Erp\OrderInfo;
    use App\Models\Erp\SiteWeb;
    use App\Models\V1\OrderE;
    use App\Models\V1\Ship;
    use App\Models\V1\ShipMethod;
    use Cassandra\Date;
    use http\Url;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use Maatwebsite\Excel\Facades\Excel;
    use Phpro\SoapClient\Exception\SoapException;
    use Phpro\SoapClient\Soap\HttpBinding\SoapRequest;
    use function GuzzleHttp\Psr7\str;

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
            $url       = $request->route()->getActionName();
            $beginTime = time();

            $info                 = DB::table('pull_log')
                                      ->where([
                                                  'pull_url' => $url,
                                                  'status'   => 1,
                                                  'type'     => 1,
                                              ])
                                      ->orderBy('current_page', 'desc')
                                      ->first('current_page');
            $page                 = empty($info) ? 1 : $info->current_page + 1;
            $params               = [];
            $params['getDetail']  = 1;
            $params['getAddress'] = 1;
            $params['page']       = $page;
            $params['pageSize']   = 50;
            $params['getAddress'] = 1;

            $service      = 'getOrderList';
            $result       = self::soapRequest($service, 'EB', $params);
            $request_time = time();
            if ( !empty($result['data'])){
                //                $siteList              = $this->getSiteList();
                $orderTotalData        = [];
                $orderTotalGoodsData   = [];
                $orderTotalAddressData = [];
                $orderShip             = [];
                foreach ($result['data'] as $key => $val) {
                    //订单
                    $referenceNo = (int)$val['saleOrderCode'];
                    //                    fp($referenceNo);
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
                    if ( !empty($val['platformShipStatus'])){
                        $ship                            = [];
                        $ship['warehouseOrderCode']      = $val['warehouseOrderCode'];
                        $ship['saleOrderCode']           = $val['saleOrderCode'];
                        $ship['shippingMethodNo']        = $val['shippingMethodNo'];
                        $ship['orderWeight']             = $val['orderWeight'];
                        $ship['shippingMethod']          = $val['shippingMethod'];
                        $ship['platformFeeTotal']        = $val['platformFeeTotal'];
                        $ship['shipFee']                 = $val['shipFee'];
                        $ship['dateWarehouseShipping']   = strtotime($val['dateWarehouseShipping']);
                        $ship['addTime']                 = \date('Y-m-d H:i:s');
                        $ship['webId']                   = $webId;
                        $ship['totalFee']                = round($val['platformFeeTotal'], 3) + round($val['shipFee'], 3);
                        $orderShip[]                     = $ship;
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
                $pullLog['type']         = 1;
                $pullLog['add_time']     = time();
                $pullData                = DB::table('pull_log')
                                             ->where([
                                                         'pull_url'     => $url,
                                                         'current_page' => $page,
                                                         'type'         => 1,
                                                     ])
                                             ->first('id');
                if (empty($pullData)){
                    $pullLog['spend_time'] = time() - $beginTime;
                    DB::beginTransaction();
                    try {
                        DB::table('ship')->insert($orderShip);
                        DB::table('e_order_goods')->insert($orderTotalGoodsData);
                        DB::table('e_address')->insert($orderTotalAddressData);
                        DB::table('pull_log')->insert($pullLog);
                        DB::table('e_orders')->insert($orderTotalData);

                        DB::commit();
                        $endTime = time();
                        ajaxReturn(200, 'success', [
                            'spend_time'   => $endTime - $beginTime,
                            'request_time' => $endTime - $request_time,
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        ajaxReturn(4001, 'error', $e->getMessage());
                        $pullLog['status']  = 0;
                        $pullLog['err_msg'] = $e->getMessage();
                        DB::table('pull_log')->insert($pullLog);

                    }
                } else {
                    $info         = [];
                    $info['page'] = $page;
                    ajaxReturn(4002, '已有该记录', $info);
                };
            } else {

                ajaxReturn(4001, 'not find data', $result);
            }

        }

        /**
         * 拉取采购单
         *
         * @param Request $request
         *
         * @throws \SoapFault
         * pullEPurchaseOrders
         * author: walker
         * Date: 2019/12/17
         * Time: 16:55
         * Note:
         */
        public function pullEPurchaseOrders(Request $request)
        {
            $url       = $request->route()->getActionName();
            $beginTime = time();

            $info               = DB::table('pull_log')
                                    ->where(['pull_url' => $url, 'status' => 1, 'type' => 2])
                                    ->orderBy('add_time', 'desc')
                                    ->first('current_page');
            $page               = empty($info) ? 1 : $info->current_page + 1;
            $pageSize           = 50;
            $service            = 'getPurchaseOrders';
            $params             = [];
            $params['page']     = $page;
            $params['pageSize'] = $pageSize;
            $result             = self::soapRequest($service, 'WMS', $params);
            if ( !empty($result)){
                $totalPurchaseOrders       = [];
                $totalPurchaseOrdersDetail = [];
                foreach ($result['data'] as $key => $val) {
                    $poId                                         = $val['po_id'];
                    $purchaseOrders                               = [];
                    $purchaseOrders['po_id']                      = $poId;
                    $purchaseOrders['po_code']                    = $val['po_code'];
                    $purchaseOrders['warehouse_id']               = $val['warehouse_id'];
                    $purchaseOrders['shipping_method_id_head']    = $val['shipping_method_id_head'];
                    $purchaseOrders['tracking_no']                = $val['tracking_no'];
                    $purchaseOrders['ref_no']                     = $val['ref_no'];
                    $purchaseOrders['suppiler_id']                = $val['suppiler_id'];
                    $purchaseOrders['payable_amount']             = round($val['payable_amount'], 3);
                    $purchaseOrders['actually_amount']            = round($val['actually_amount'], 3);
                    $purchaseOrders['pay_ship_amount']            = round($val['pay_ship_amount'], 3);
                    $purchaseOrders['sum_amount']                 = round($val['sum_amount'], 3);
                    $purchaseOrders['total_tax_fee']              = round($val['total_tax_fee'], 3);
                    $purchaseOrders['currency_code']              = $val['currency_code'] ?: null;
                    $purchaseOrders['pay_status']                 = $val['pay_status'] ?: null;
                    $purchaseOrders['po_staus']                   = $val['po_staus'] ?: null;
                    $purchaseOrders['date_create']                = strtotime($val['date_create']);
                    $purchaseOrders['date_eta']                   = strtotime($val['date_eta']);
                    $purchaseOrders['date_release']               = strtotime($val['date_release']);
                    $purchaseOrders['po_completion_time']         = strtotime($val['po_completion_time']);
                    $purchaseOrders['to_warehouse_id']            = $val['to_warehouse_id'] ?: null;
                    $purchaseOrders['receiving_exception']        = $val['receiving_exception'] ?: null;
                    $purchaseOrders['operator_purchase']          = $val['operator_purchase'] ?: null;
                    $purchaseOrders['receiving_exception_handle'] = $val['receiving_exception_handle'] ?: null;
                    $purchaseOrders['return_verify']              = $val['return_verify'] ?: null;
                    $purchaseOrders['create_type']                = $val['create_type'] ?: null;
                    $purchaseOrders['pts_status_sort']            = $val['pts_status_sort'] ?: null;
                    $purchaseOrders['account_type']               = $val['account_type'] ?: null;
                    $purchaseOrders['pts_oprater']                = $val['pts_oprater'] ?: null;
                    $purchaseOrders['transaction_no']             = $val['transaction_no'] ?: null;
                    $purchaseOrders['ps_id']                      = $val['ps_id'] ?: null;
                    $purchaseOrders['po_remark']                  = $val['po_remark'] ?: null;
                    $purchaseOrders['receiving_exception_status'] = $val['receiving_exception_status'] ?: null;
                    $purchaseOrders['qc_exception_status']        = $val['qc_exception_status'] ?: null;
                    $purchaseOrders['supplier_name']              = $val['supplier_name'] ?: null;
                    $purchaseOrders['supplier_code']              = $val['supplier_code'] ?: null;
                    $purchaseOrders['warehouse_code']             = $val['warehouse_code'] ?: null;
                    $purchaseOrders['warehouse_desc']             = $val['warehouse_desc'] ?: null;
                    $purchaseOrders['receiving_code']             = $val['receiving_code'] ?: null;
                    $purchaseOrders['verify']                     = $val['verify'] ?: null;
                    $purchaseOrders['mark_eta']                   = $val['mark_eta'] ?: null;
                    $purchaseOrders['pts_name']                   = $val['pts_name'] ?: null;
                    //                    $purchaseOrders['ps_name']                    = $val['ps_name'];
                    //                    $purchaseOrders['ps_url']                     = $val['ps_url'];
                    $purchaseOrders['pt_note']          = $val['pt_note'] ?: null;
                    $purchaseOrders['qty_expected_all'] = $val['qty_expected_all'] ?: null;
                    $purchaseOrders['qty_receving_all'] = $val['qty_receving_all'] ?: null;
                    $purchaseOrders['trackings']        = $val['trackings'] ?: null;
                    $purchaseOrders['po_is_net']        = $val['po_is_net'] ?: null;
                    $purchaseOrders['pay_type']         = $val['pay_type'] ?: null;
                    //                    $purchaseOrders['bank_name']                  = $val['bank_name'];
                    //                    $purchaseOrders['pay_account']                = $val['pay_account'];
                    $purchaseOrders['date_expected']     = $val['date_expected'] ?: null;
                    $purchaseOrders['po_type']           = $val['po_type'] ?: null;
                    $purchaseOrders['single_net_number'] = json_encode($val['single_net_number'], true);
                    $purchaseOrders['track']             = json_encode($val['track'], true);
                    $purchaseOrders['payment_note']      = $val['payment_note'] ?: null;
                    $purchaseOrders['company']           = $val['company'] ?: null;
                    $totalPurchaseOrders[]               = $purchaseOrders;
                    //采购单详情
                    if ( !empty($val['detail'])){
                        foreach ($val['detail'] as $key1 => $val1) {
                            $detail                 = [];
                            $detail['po_id']        = $poId;
                            $detail['product_id']   = $val1['product_id'];
                            $detail['qty_expected'] = $val1['qty_expected'];
                            $detail['qty_pay']      = $val1['qty_pay'];
                            $detail['qty_eta']      = $val1['qty_eta'];
                            $detail['qty_receving'] = $val1['qty_receving'];
                            $detail['qty_free']     = $val1['qty_free'];
                            $detail['unit_price']   = round($val1['unit_price'], 3);
                            $detail['total_price']  = round($val1['total_price'], 3);

                            $detail['currency_code']       = $val1['currency_code'];
                            $detail['product_sku']         = $val1['product_sku'];
                            $detail['product_title']       = $val1['product_title'];
                            $detail['sp_supplier_sku']     = $val1['sp_supplier_sku'];
                            $detail['is_free']             = $val1['is_free'];
                            $detail['note']                = $val1['note'];
                            $detail['pop_external_number'] = $val1['pop_external_number'];
                            $detail['transfer_qty']        = $val1['transfer_qty'];
                            $detail['po_tax_rate']         = $val1['po_tax_rate'];
                            $detail['first_receive_time']  = strtotime($val1['first_receive_time']);
                            $detail['add_time']            = \date('Y-m-d H:i:s');
                            $totalPurchaseOrdersDetail[]   = $detail;
                        }
                    }
                }
                $pullLog                 = [];
                $pullLog['pull_url']     = $url;
                $pullLog['pull_time']    = \date('Y/m/d H:i:s');
                $pullLog['count']        = $result['totalCount'];
                $pullLog['page_size']    = $result['pageSize'];
                $pullLog['current_page'] = $page;
                $pullLog['status']       = 1;
                $pullLog['type']         = 2;
                $pullLog['add_time']     = time();
                $pullData                = DB::table('pull_log')
                                             ->where(['pull_url' => $url, 'current_page' => $page])
                                             ->first('id');
                if (empty($pullData)){
                    DB::beginTransaction();
                    try {
                        DB::table('e_purchase_orders')->insert($totalPurchaseOrders);
                        DB::table('e_purchase_orders_detail')->insert($totalPurchaseOrdersDetail);
                        $pullLog['spend_time'] = time() - $beginTime;
                        DB::table('pull_log')->insert($pullLog);
                        DB::commit();
                        $endTime = time();
                        ajaxReturn(200, 'success', ['spend_time' => $endTime - $beginTime]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $pullLog['status']  = 0;
                        $pullLog['err_msg'] = $e->getMessage();
                        DB::table('pull_log')->insert($pullLog);
                        ajaxReturn(4002, 'error', $e->getMessage());
                    }
                };
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
            } else {
                //                            $model = new OrderInfo();
                $model = DB::table('shopify_order');
                $info  = $model->where(['shopify_id' => $referenceNo])
                               ->first('web_id');
                if ( !empty($info)){
                    return $info->web_id;
                } else {
                    return false;
                }
            }
        }


        /**
         * 获取采购费用
         *
         * @param Request $request
         * pullOrderCostDetail
         * author: walker
         * Date: 2019/12/20
         * Time: 15:59
         * Note:
         */
        public function pullOrderCostDetail(Request $request)
        {
            $url       = $request->route()->getActionName();
            $beginTime = time();

            $info          = DB::table('pull_log')
                               ->where(['pull_url' => $url, 'status' => 1, 'type' => 3])
                               ->orderBy('add_time', 'desc')
                               ->first('current_page');
            $page          = empty($info) ? 1 : $info->current_page + 1;
            $pageSize      = 100;
            $service       = 'getOrderCostDetail';
            $orderCodeList = DB::table('e_orders')
                               ->where('warehouseOrderCode', '!=', '')
                               ->where(['pull_cost' => 0])
                               ->selectRaw('warehouseOrderCode,pull_cost')
                               ->limit($pageSize)
                               ->get();
            $orderCodeList = toArr($orderCodeList);
            $orderCode     = [];
            foreach ($orderCodeList as $key1 => $val1) {
                $orderCode[] = $val1['warehouseOrderCode'];
            }
            if ( !empty($orderCode)){
                $params              = [];
                $params['orderCode'] = $orderCode;
                $result              = self::soapRequest($service, 'WMS', $params);
                if ( !empty($result)){
                    $orderCostLists = [];
                    foreach ($result['data'] as $key => $val) {
                        $webId                              = DB::table('e_orders')->where(['warehouseOrderCode' => $val['reference_no']])->first('webId');
                        $webId                              = $webId->webId ?: 0;
                        $orderCost                          = [];
                        $orderCost['order_platform_type']   = $val['order_platform_type'];
                        $orderCost['reference_no']          = $val['reference_no'];
                        $orderCost['orderTotalAmount']      = round($val['orderTotalAmount'], 3);
                        $orderCost['productAmount']         = round($val['productAmount'], 3);
                        $orderCost['buyerPayShipping']      = round($val['buyerPayShipping'], 3);
                        $orderCost['shippingFee']           = round($val['shippingFee'], 3);
                        $orderCost['paymentPlatformFee']    = round($val['paymentPlatformFee'], 3);
                        $orderCost['platformCost']          = round($val['platformCost'], 3);
                        $orderCost['fbaFee']                = round($val['fbaFee'], 3);
                        $orderCost['otherFee']              = round($val['otherFee'], 3);
                        $orderCost['packageFee']            = round($val['packageFee'], 3);
                        $orderCost['purchaseCost']          = round($val['purchaseCost'], 3);
                        $orderCost['purchaseShippingFee']   = round($val['purchaseShippingFee'], 3);
                        $orderCost['purchaseTaxationFee']   = round($val['purchaseTaxationFee'], 3);
                        $orderCost['serviceTransportFee']   = round($val['serviceTransportFee'], 3);
                        $orderCost['currency_rate']         = round($val['currency_rate'], 3);
                        $orderCost['firstCarrierFreight']   = round($val['firstCarrierFreight'], 3);
                        $orderCost['tariffFee']             = round($val['tariffFee'], 3);
                        $orderCost['orderTotalAmountOrg']   = round($val['orderTotalAmountOrg'], 3);
                        $orderCost['productAmountOrg']      = round($val['productAmountOrg'], 3);
                        $orderCost['buyerPayShippingOrg']   = round($val['buyerPayShippingOrg'], 3);
                        $orderCost['paymentPlatformFeeOrg'] = round($val['paymentPlatformFeeOrg'], 3);
                        $orderCost['platformCostOrg']       = round($val['platformCostOrg'], 3);
                        $orderCost['paymentPlatformFeeOrg'] = round($val['paymentPlatformFeeOrg'], 3);
                        $orderCost['fbaFeeOrg']             = round($val['fbaFeeOrg'], 3);
                        $orderCost['otherFeeOrg']           = round($val['otherFeeOrg'], 3);
                        $orderCost['shippingFeeOrg']        = round($val['shippingFeeOrg'], 3);
                        $orderCost['paymentPlatformFeeOrg'] = round($val['paymentPlatformFeeOrg'], 3);
                        $orderCost['totalCost']             = round($val['totalCost'], 3);
                        $orderCost['grossProfit']           = round($val['grossProfit'], 3);
                        $orderCost['grossProfitRate']       = round($val['grossProfitRate'], 3);


                        $orderCost['currencyCodeOrg'] = $val['currencyCodeOrg'];
                        $orderCost['sku_quantity']    = $val['sku_quantity'];
                        $orderCost['currencyCode']    = $val['currencyCode'];
                        $orderCost['pay_time']        = $val['pay_time'];

                        $orderCost['web_id']   = $webId;
                        $orderCost['add_time'] = \date('Y-m-d H:i:s');
                        $orderCostLists[]      = $orderCost;

                    }
                    $pullLog                 = [];
                    $pullLog['pull_url']     = $url;
                    $pullLog['pull_time']    = \date('Y/m/d H:i:s');
                    $pullLog['count']        = $pageSize;
                    $pullLog['page_size']    = $pageSize;
                    $pullLog['current_page'] = $page;
                    $pullLog['status']       = 1;
                    $pullLog['type']         = 3;
                    $pullLog['add_time']     = time();
                    $pullData                = DB::table('pull_log')
                                                 ->where(['pull_url' => $url, 'current_page' => $page])
                                                 ->first('id');
                    if (empty($pullData)){
                        DB::beginTransaction();
                        try {
                            DB::table('e_orders')->whereIn('warehouseOrderCode', $orderCode)->update(['pull_cost' => 1]);
                            DB::table('e_order_cost')->insert($orderCostLists);
                            $pullLog['spend_time'] = time() - $beginTime;
                            DB::table('pull_log')->insert($pullLog);
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
                    ajaxReturn(4002, '请求接口失败', $result);
                }
            } else {
                ajaxReturn(4003, '没有订单号', $orderCode);
            }

        }

        /**
         * 获取采购费用按SKU
         *
         * @param Request $request
         *
         * @throws \SoapFault
         * pullProductSkuCost
         * author: walker
         * Date: 2019/12/24
         * Time: 18:18
         * Note:
         */
        public function pullProductSkuCost(Request $request)
        {
            $url       = $request->route()->getActionName();
            $beginTime = time();
            $service   = 'getOrderCostDetailSku';

            $where              = [
                'pull_url' => $url,
                'status'   => 1,
                'type'     => 4,
            ];
            $info               = DB::table('pull_log')
                                    ->where($where)
                                    ->orderBy('current_page', 'desc')
                                    ->first('current_page');
            $page               = empty($info) ? 1 : $info->current_page + 1;
            $params             = [];
            $params['page']     = $page;
            $params['pageSize'] = 50;
            $params['dateFor']  = "2018-03-18";
            $params['dateTo']   = \date("Y-m-d");
            $logTable           = DB::table('pull_log');
            $result             = self::soapRequest($service, 'WMS', $params);

            $pullLogData                 = [];
            $pullLogData['pull_url']     = $url;
            $pullLogData['current_page'] = $page;
            $pullLogData['pull_time']    = \date('Y/m/d H:i:s');
            $pullLogData['add_time']     = time();
            $pullLogData['type']         = 4;
            if ( !empty($result)){
                if ( !empty($result['data'])){
                    $totalGoodsCost = [];
                    foreach ($result['data'] as $key => $val) {
                        $goodsCost                               = [];
                        $webId                                   = $this->getWebId($val['platformReferenceNo']);
                        $goodsCost['platform']                   = $val['platform'];
                        $goodsCost['orderPlatformType']          = $val['orderPlatformType'];
                        $goodsCost['referenceNo']                = $val['referenceNo'];
                        $goodsCost['platformReferenceNo']        = $val['platformReferenceNo'];
                        $goodsCost['receivingCode']              = $val['receivingCode'];
                        $goodsCost['productId']                  = $val['productId'];
                        $goodsCost['orderSaleType']              = $val['orderSaleType'];
                        $goodsCost['siteId']                     = $val['siteId'];
                        $goodsCost['sellerId']                   = $val['sellerId'];
                        $goodsCost['warehouseId']                = $val['warehouseId'];
                        $goodsCost['productBarcode']             = $val['productBarcode'];
                        $goodsCost['opPlatformSalesSku']         = $val['opPlatformSalesSku'];
                        $goodsCost['opPlatformSalesSkuQuantity'] = $val['opPlatformSalesSkuQuantity'];
                        $goodsCost['quantity']                   = $val['quantity'];
                        $goodsCost['productTitle']               = $val['productTitle'];
                        $goodsCost['orderTotalAmount']           = round($val['orderTotalAmount'], 3);
                        $goodsCost['productAmount']              = round($val['productAmount'], 3);
                        $goodsCost['orderTotalAmount']           = round($val['orderTotalAmount'], 3);
                        $goodsCost['buyerPayShipping']           = round($val['buyerPayShipping'], 3);
                        $goodsCost['ebaySellerRebate']           = round($val['ebaySellerRebate'], 3);
                        $goodsCost['shippingFee']                = round($val['shippingFee'], 3);
                        $goodsCost['paymentPlatformFee']         = round($val['paymentPlatformFee'], 3);
                        $goodsCost['platformCost']               = round($val['platformCost'], 3);
                        $goodsCost['fbaFee']                     = round($val['fbaFee'], 3);
                        $goodsCost['packageFee']                 = round($val['packageFee'], 3);
                        $goodsCost['warehouseStorageCharges']    = round($val['warehouseStorageCharges'], 3);
                        $goodsCost['processingFee']              = round($val['processingFee'], 3);
                        $goodsCost['otherFee']                   = round($val['otherFee'], 3);
                        $goodsCost['purchaseShippingFee']        = round($val['purchaseShippingFee'], 3);
                        $goodsCost['purchaseTaxationFee']        = round($val['purchaseTaxationFee'], 3);
                        $goodsCost['purchaseCost']               = round($val['purchaseCost'], 3);
                        $goodsCost['serviceTransportFee']        = round($val['serviceTransportFee'], 3);
                        $goodsCost['currency_rate']              = round($val['currency_rate'], 3);
                        $goodsCost['referencePrice']             = round($val['referencePrice'], 3);
                        $goodsCost['avgUnitPrice']               = round($val['avgUnitPrice'], 3);
                        $goodsCost['avgPurchasePrice']           = round($val['avgPurchasePrice'], 3);
                        $goodsCost['firstCarrierFreight']        = round($val['firstCarrierFreight'], 3);
                        $goodsCost['tariffFee']                  = round($val['tariffFee'], 3);
                        $goodsCost['orderTotalAmountOrg']        = round($val['orderTotalAmountOrg'], 3);
                        $goodsCost['productAmountOrg']           = round($val['productAmountOrg'], 3);
                        $goodsCost['buyerPayShippingOrg']        = round($val['buyerPayShippingOrg'], 3);
                        $goodsCost['shippingFeeOrg']             = round($val['shippingFeeOrg'], 3);
                        $goodsCost['paymentPlatformFeeOrg']      = round($val['paymentPlatformFeeOrg'], 3);
                        $goodsCost['platformCostOrg']            = round($val['platformCostOrg'], 3);
                        $goodsCost['fbaFeeOrg']                  = round($val['fbaFeeOrg'], 3);
                        $goodsCost['packageFeeOrg']              = round($val['packageFeeOrg'], 3);
                        $goodsCost['warehouseStorageChargesOrg'] = round($val['warehouseStorageChargesOrg'], 3);
                        $goodsCost['avgUnitPriceOrg']            = round($val['avgUnitPriceOrg'], 3);
                        $goodsCost['processingFeeOrg']           = round($val['processingFeeOrg'], 3);
                        $goodsCost['otherFeeOrg']                = round($val['otherFeeOrg'], 3);
                        $goodsCost['ebaySellerRebateOrg']        = round($val['ebaySellerRebateOrg'], 3);
                        $goodsCost['currencyCodeOrg']            = round($val['currencyCodeOrg'], 3);
                        $goodsCost['currencyCode']               = round($val['currencyCode'], 3);
                        $goodsCost['totalCost']                  = round($val['totalCost'], 3);
                        $goodsCost['grossProfit']                = round($val['grossProfit'], 3);
                        $goodsCost['grossProfitRate']            = round($val['grossProfitRate'], 3);
                        $goodsCost['factoryGrossProfit']         = round($val['factoryGrossProfit'], 3);
                        $goodsCost['factoryGrossMargin']         = round($val['factoryGrossMargin'], 3);
                        $goodsCost['asinOrItem']                 = $val['asinOrItem'];
                        $goodsCost['destinationCountry']         = $val['destinationCountry'];
                        $goodsCost['dateRelease']                = $val['dateRelease'];
                        $goodsCost['soShipTime']                 = $val['soShipTime'];
                        $goodsCost['developResponsibleName']     = $val['developResponsibleName'];
                        $goodsCost['sellerResponsibleName']      = $val['sellerResponsibleName'];
                        $goodsCost['buyerName']                  = $val['buyerName'];
                        $goodsCost['smCode']                     = $val['smCode'];

                        $goodsCost['pay_time']   = $val['pay_time'];
                        $goodsCost['updateTime'] = $val['updateTime'];
                        $goodsCost['web_id']     = $webId;
                        $totalGoodsCost[]        = $goodsCost;
                    }
                    $pullLogData['current_page'] = $result['page'];
                    $pullLogData['page_size']    = $result['pageSize'];
                    $pullLogData['count']        = $result['totalCount'];
                    $pullLogData['spend_time']   = time() - $beginTime;
//                    $logInfo                     = $logTable->where($where)->first('id');
                    DB::beginTransaction();
                    try {
                        DB::table('e_order_goods_cost')->insert($totalGoodsCost);
                        $pullLogData['status']  = 1;
                        $pullLogData['err_msg'] = '下载订单费用和成本明细(按SKU)成功';

                            DB::table('pull_log')->insert($pullLogData);
                        DB::commit();
                        ajaxReturn(200, '下载订单费用和成本明细(按SKU)成功',['spend_time'=>time()-$beginTime]);
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        $pullLogData['err_msg'] = $exception->getMessage();
                        $pullLogData['status']  = 0;
//                        if (empty($logInfo)){
//                            $logTable->insert($pullLogData);
//                        } else {
//                            $id = $logInfo->id;
//                            $logTable->where(['id' => $id])->update($pullLogData);
//                        }
                        ajaxReturn(4003, '添加数据失败', $exception->getMessage());
                    }
                } else {
                    ajaxReturn(4002, '到头了');
                }
                fp($result);
            } else {
                $where['current_page']  = $page;
                $pullLogData['err_msg'] = '没有获取到数据' . $result;
                $where['status']        = 0;
//                $logInfo                = $logTable->where($where)->first('id');
//                if (empty($logInfo)){
//                    $pullLogData['spend_time'] = time() - $beginTime;
//                    $logTable->insert($pullLogData);
//                } else {
//                    $id = $logInfo->id;
//                    $logTable->where(['id' => $id])->update($pullLogData);
//                }
                ajaxReturn(4001, '没有获取到数据' . $result);
            }
            //            fp($result);
        }

        /**
         * 费用总数据走势图
         *
         * @param Request $request
         * getTotalFeeData
         * author: walker
         * Date: 2019/12/20
         * Time: 15:57
         * Note:
         */
        public function getTotalFeeData(Request $request)
        {
            $request->validate([
                                   //                                   'web_id' => 'required|string|max:30|unique:menu',
                               ]);
            //分组条件 1天内按小时分组,否则按天/月分组
            //86400/1天 2678400/1月
            $start    = strtotime('-1 year');
            $end      = time();
            $diff     = $end - $start;
            $where    = [];
            $whereWeb = [];
            if ($diff < 86400 && $diff > 0){
                $sort = '%H';
            } elseif ($diff < 2678400) {
                $sort = '%Y-%m-%d';
            } else {
                $sort = '%Y-%m';
            }
            if ( !empty($request->web_id)){
                $where['web_id']   = $request->web_id;
                $whereWeb['webId'] = $request->web_id;
            }
            //物流费用统计
            $shipFee = DB::table('ship')
                         ->select(DB::raw("FROM_UNIXTIME(dateWarehouseShipping,'{$sort}') as create_time,sum(totalFee) as total_price"))
                         ->groupBy(DB::raw("FROM_UNIXTIME(dateWarehouseShipping,'{$sort}')"))
                         ->orderBy('create_time', 'asc')
                         ->where($whereWeb)
                         ->whereBetween('dateWarehouseShipping', [$start, $end])
                         ->get();
            $shipFee = toArr($shipFee);
            //物料费用
            $material = DB::table('material')
                          ->select(DB::raw("FROM_UNIXTIME(add_time,'{$sort}') as create_time,sum(total_price) as total_price  "))
                          ->groupBy(DB::raw("FROM_UNIXTIME(add_time,'{$sort}')"))
                          ->orderBy('create_time', 'asc')
                          ->where($where)
                          ->whereBetween('add_time', [$start, $end])
                          ->get();
            $material = toArr($material);
            //采购费用
            $orderCost = DB::table('e_order_cost')
                           ->select(DB::raw("date_format(pay_time,'{$sort}') as create_time,sum(totalCost) as total_price"))
                           ->groupBy(DB::raw("date_format(pay_time,'{$sort}')"))
                           ->orderBy('create_time', 'asc')
                           ->where($where)
                           ->whereBetween('pay_time', [\date('Y-m-d H:i:s', $start), \date('Y-m-d H:i:s', $end)])
                           ->get();
            $orderCost = toArr($orderCost);
            $year
                       = date('Y', time());
            #一年的月份
            $month        = [
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
            $data         = [];
            $materialNew  = [];
            $orderCostNew = [];
            $shipFeeNew   = [];
            foreach ($material as $key1 => $val1) {
                $materialNew[$val1['create_time']] = $val1['total_price'];
            }
            foreach ($orderCost as $key2 => $val2) {
                $orderCostNew[$val2['create_time']] = $val2['total_price'];
            }
            foreach ($shipFee as $key3 => $val3) {
                $shipFeeNew[$val3['create_time']] = $val3['total_price'];
            }
            foreach ($month as $key => $val) {
                $data['material'][$key]['date'] = $val;
                if (array_key_exists($val, $materialNew)){
                    $data['material'][$key]['total_price'] = $materialNew[$val];
                } else {
                    $data['material'][$key]['total_price'] = 0;
                };
                $data['order_cost'][$key]['date'] = $val;
                if (array_key_exists($val, $orderCostNew)){
                    $data['order_cost'][$key]['total_price'] = $orderCostNew[$val];
                } else {
                    $data['order_cost'][$key]['total_price'] = 0;
                };
                $data['ship_fee'][$key]['date'] = $val;
                if (array_key_exists($val, $shipFeeNew)){
                    $data['ship_fee'][$key]['total_price'] = $shipFeeNew[$val];
                } else {
                    $data['ship_fee'][$key]['total_price'] = 0;
                };
                //                fp($val);
            }


            ajaxReturn(200, Code::$com[200], $data);

        }

        /**
         * 费用总数据
         *
         * @param Request $request
         * getTotalFee
         * author: walker
         * Date: 2019/12/24
         * Time: 14:25
         * Note:
         */
        public function getTotalFee(Request $request)
        {
            $start = $request->start_time ? strtotime($request->start_time) : strtotime('-1 year');
            $end   = $request->end_time ? strtotime($request->end_time) : time();
            $where = [];
            if ( !empty($request->web_id)){
                $where['web_id'] = $request->web_id;
            }

            $totalOrderSale = DB::table('shopify_order')
                                ->whereBetween('created_at', [$start, $end])
                                ->select(DB::raw("sum(total_price_usd) as total_pirce"))
                                ->where($where)
                                ->first();
            $totalOrderSale = $totalOrderSale->total_pirce ?: 0;//订单总销售额
            $countUser      = DB::table('shopify_order')
                                ->whereBetween('created_at', [$start, $end])
                                ->select(DB::raw("count(email) as user_nums"))
                                ->groupBy('email')
                                ->where($where)
                                ->first();
            $countUser      = $countUser->user_nums ?: 0;
            //客单价
            $pct = $totalOrderSale / $countUser;
            //物流费用
            $ship = DB::table('ship');
            if ( !empty($request->web_id)){
                $ship->where(['webId' => $request->web_id]);
            }
            $shipTotalFee = $ship
                ->select(DB::raw("sum(totalFee) as total_price"))
                ->whereBetween('dateWarehouseShipping', [$start, $end])
                ->first();
            $shipTotalFee = $shipTotalFee->total_price ?: 0;
            //物料费用
            $materialTotalFee = DB::table('material')
                                  ->where($where)
                                  ->where(['is_del' => 0, 'status' => 1])
                                  ->select(DB::raw("sum(total_price) as total_price"))
                                  ->whereBetween('buy_time', [$start, $end])
                                  ->first();
            $materialTotalFee = $materialTotalFee->total_price ?: 0;
            //采购费用
            $orderCostTotal = DB::table('e_order_cost')
                                ->where($where)
                                ->whereBetween('pay_time', [\date('Y-m-d H:i:s', $start), \date('Y-m-d H:i:s', $end)])
                                ->select(DB::raw("sum(totalCost) as total_price"))
                                ->first();
            $orderCostTotal = $orderCostTotal->total_price ?: 0;
            $totalCost      = $shipTotalFee + $materialTotalFee + $orderCostTotal;

            //date_format  FROM_UNIXTIME1
            $data['total_cost']  = $totalCost;
            $data['order_sales'] = $totalOrderSale;
            $data['pct']         = $pct;
            ajaxReturn(200, Code::$com[200], $data);

        }

        /**
         * 获取物流费用
         *
         * @throws \SoapFault
         * getShippingMethod
         * author: walker
         * Date: 2019/12/17
         * Time: 14:52
         * Note:
         */
        public function getShippingMethod()
        {
            $result      = $this->getShippingType();
            $table       = new ShipMethod();
            $shipMethods = $table->get()->toArray();
            if (empty($shipMethods) && $result){
                $table->insert($result);
            };
            ajaxReturn(200, Code::$com[200], $result);
        }


        /**
         * 获取物流方式字典
         *
         * @return array|mixed
         * @throws \SoapFault
         * getShippingMethodMap
         * author: walker
         * Date: 2019/12/17
         * Time: 14:48
         * Note:
         */
        public function getShippingMethodMap()
        {
            $mapList = Cache::get('ship_method_map');
            if (empty($mapList)){
                $mapList    = [];
                $methodList = $this->getShippingType();
                foreach ($methodList as $key => $val) {
                    $mapList[$val['sm_code']] = $val['sm_name_cn'];
                }
                Cache::add('ship_method_map', $mapList);
            }
            return $mapList;

        }

        /**
         * 获取物流方式列表
         *
         * @return array|mixed
         * @throws \SoapFault
         * getShippingType
         * author: walker
         * Date: 2019/12/17
         * Time: 14:39
         * Note:
         */
        public function getShippingType()
        {
            $result = Cache::get('shippingMethod');
            if (empty($result)){
                $service = 'getShippingMethod';
                $result  = self::soapRequest($service, 'WMS');
                $result  = $result['data'];
                Cache::add('shippingMethod', $result);
            }
            return $result;

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
