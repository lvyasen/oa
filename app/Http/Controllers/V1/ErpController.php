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
            $url = $request->route()->getActionName();
            $this->pullOrderList($url);
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
                        DB::beginTransaction();
                        DB::table('e_purchase_orders')->insert($totalPurchaseOrders);
                        DB::rollBack();
                        DB::table('e_purchase_orders_detail')->insert($totalPurchaseOrdersDetail);
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
            //            $model = new OrderInfo();
            $model = DB::table('shopify_order');
            $info  = $model->where(['shopify_id' => $referenceNo])
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
            $page            = (int)$request->page ?: 1;
            $pageNum         = $request->pageNum ?: 10;
            $pageStart       = ($page - 1) * $pageNum;
            $webId           = $request->web_id;
            $where           = [];
            $where['status'] = 1;
            $startTime       = $request->start_time ? strtotime($request->start_time) : 0;
            $endTime         = $request->end_time ? strtotime($request->end) : time();
            if ( !empty($webId)) $where['web_id'] = $webId;
            $table = DB::table('ship');
            $table->whereBetween('dateWarehouseShipping', [$startTime, $endTime]);
            $list          = $table
                ->where($where)
                ->offset($pageStart)
                ->orderBy('dateWarehouseShipping', 'desc')
                ->limit($pageNum)
                ->get();
            $count         = $table->where($where)->count();
            $list          = toArr($list);
            $shipMethodMap = $this->getShippingMethodMap();
            foreach ($list as $key => $val) {
                $list[$key]['shippingMethod'] = $shipMethodMap[$val['shippingMethod']];
            }
            if ( !empty($request->download)){
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
         *
         * @param Request $request
         * getLogisticsLineChart
         * author: walker
         * Date: 2019/12/16
         * Time: 16:21
         * Note:
         */
        public function getLogisticsLineChart(Request $request)
        {

            $where           = [];
            $where['status'] = 1;
            if ( !empty($request->web_id)) $where['webId'] = $request->web_id;

            $orderList = DB::table('ship')
                           ->where($where)
                           ->orderBy('dateWarehouseShipping', 'desc')
                           ->selectRaw('shipFee,dateWarehouseShipping,webId')
                           ->get();
            $orderList = toArr($orderList);
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
                    if ($val == \date('Y-m', $val1['dateWarehouseShipping'])){
                        $data[$key]['value'] = round($data[$key]['value'], 2) + round($val1['shipFee'], 2);
                    };
                }
            }
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 添加物流费用
         *
         * @param Request $request
         * addLogistics
         * author: walker
         * Date: 2019/12/17
         * Time: 14:53
         * Note:
         */
        public function addLogistics(Request $request)
        {
            $request->validate([
                                   'warehouseOrderCode'    => 'required|string',
                                   'shippingMethodNo'      => 'required|string',
                                   'orderWeight'           => 'required|string',
                                   'shippingMethod'        => 'required|string',
                                   'platformFeeTotal'      => 'required|string',
                                   'shipFee'               => 'required|string',
                                   'dateWarehouseShipping' => 'required|date',
                                   'webId'                 => 'required|string',
                               ]);

            $model                        = new Ship();
            $model->warehouseOrderCode    = $request->warehouseOrderCode;
            $model->shippingMethodNo      = $request->shippingMethodNo;
            $model->orderWeight           = round($request->orderWeight, 2);
            $model->shippingMethod        = $request->shippingMethod;
            $model->platformFeeTotal      = round($request->platformFeeTotal, 2);
            $model->shipFee               = round($request->shipFee, 2);
            $model->dateWarehouseShipping = strtotime($request->dateWarehouseShipping);
            $model->webId                 = $request->webId;
            $model->type                  = 1;
            $model->totalFee              = round($request->platformFeeTotal, 2) + round($request->shipFee, 2);
            $result                       = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加物流费用');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改物流费用
         *
         * @param Request $request
         * editLogistics
         * author: walker
         * Date: 2019/12/17
         * Time: 15:22
         * Note:
         */
        public function editLogistics(Request $request)
        {
            $request->validate([
                                   'id' => 'required|string|exists:ship',
                               ]);

            $model                        = Ship::find($request->id);
            $model->warehouseOrderCode    = $request->warehouseOrderCode;
            $model->shippingMethodNo      = $request->shippingMethodNo;
            $model->orderWeight           = round($request->orderWeight, 2);
            $model->shippingMethod        = $request->shippingMethod;
            $model->platformFeeTotal      = round($request->platformFeeTotal, 2);
            $model->shipFee               = round($request->shipFee, 2);
            $model->dateWarehouseShipping = strtotime($request->dateWarehouseShipping);
            $model->webId                 = $request->webId;
            //            $model->type                  = 1;
            $model->totalFee = round($request->platformFeeTotal, 2) + round($request->shipFee, 2);
            $result          = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改物流信息');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 删除物流费用
         *
         * @param Request $request
         * delLogistics
         * author: walker
         * Date: 2019/12/17
         * Time: 15:26
         * Note:
         */
        public function delLogistics(Request $request)
        {
            $request->validate([
                                   'id' => 'required|string|exists:ship',
                               ]);

            $model         = Ship::find($request->id);
            $model->status = 0;
            $result        = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '删除物流费用');
            ajaxReturn(200, Code::$com[200]);
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

            $info                = DB::table('pull_log')
                                     ->where(['pull_url' => $url, 'status' => 1, 'type' => 3])
                                     ->orderBy('add_time', 'desc')
                                     ->first('current_page');
            $page                = empty($info) ? 1 : $info->current_page + 1;
            $pageSize            = 50;
            $service             = 'getOrderCostDetail';
            $params              = [];
            $params['orderCode'] = ['SF19032738472', 'SF19032738386'];
            $params['page']      = $page;
            $params['pageSize']  = $pageSize;
            $result              = self::soapRequest($service, 'WMS', $params);
            fp($result);
            if ( !empty($result)){
                $orderCostLists = [];
                foreach ($result['data'] as $key => $val) {
                    $webId                              = DB::table('e_orders')->where(['warehouseOrderCode' => $val['reference_no']])->first('webId');
                    $webId                              = $webId ? $webId->web_id : 0;
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

                    $orderCost['web_id']        = $webId;
                    $orderCost['add_time']        = \date('Y-m-d H:i:s');
                    $orderCostLists[] = $orderCost;

                }
                $pullLog                 = [];
                $pullLog['pull_url']     = $url;
                $pullLog['pull_time']    = \date('Y/m/d H:i:s');
                $pullLog['count']        = $result['totalCount'];
                $pullLog['page_size']    = $result['pageSize'];
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
            }
        }

        /**
         * 费用总数据
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
            //物流费用
            //物料费用
            //采购费用
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
                      ->where(['pull_url' => $url, 'status' => 1, 'type' => 1])
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
            fp($result);

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
                        $ship                          = [];
                        $ship['warehouseOrderCode']    = $val['warehouseOrderCode'];
                        $ship['saleOrderCode']         = $val['saleOrderCode'];
                        $ship['shippingMethodNo']      = $val['shippingMethodNo'];
                        $ship['orderWeight']           = $val['orderWeight'];
                        $ship['shippingMethod']        = $val['shippingMethod'];
                        $ship['platformFeeTotal']      = $val['platformFeeTotal'];
                        $ship['shipFee']               = $val['shipFee'];
                        $ship['dateWarehouseShipping'] = strtotime($val['dateWarehouseShipping']);
                        $ship['addTime']               = \date('Y-m-d H:i:s');
                        $ship['webId']                 = $webId;
                        $ship['totalFee']              = round($val['platformFeeTotal'], 3) + round($val['shipFee'], 3);
                        $orderShip[]                   = $ship;
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
                                             ->where(['pull_url' => $url, 'current_page' => $page])
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
                        ajaxReturn(200, 'success', ['spend_time' => $endTime - $beginTime]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        ajaxReturn(4002, 'error', $e->getMessage());
                        $pullLog['status']  = 0;
                        $pullLog['err_msg'] = $e->getMessage();
                        DB::table('pull_log')->insert($pullLog);
                        DB::commit();

                    }
                };
            } else {

                ajaxReturn(4001, 'not find data');
            }

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
        private function getShippingMethodMap()
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
        private function getShippingType()
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
