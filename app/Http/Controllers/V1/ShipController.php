<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Exports\LogisticsExport;
    use App\Http\Controllers\Controller;
    use App\Models\Erp\OrderInfo;
    use App\Models\V1\Ship;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Maatwebsite\Excel\Facades\Excel;

    class ShipController extends Controller
    {
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
            $table           = new Ship();
            $where['status'] = 1;
            $table->whereBetween('dateWarehouseShipping', [$startTime, $endTime]);
            $list          = $table->where($where)->offset($pageStart)->limit($pageNum)->get();
            $count         = $table->where($where)->count();
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
            if ( !empty($webId)) $where['webId'] = $webId;
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
            $model         = new  ErpController();
            $shipMethodMap = $model->getShippingMethodMap();
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
         * 运费数据补充
         * @param Request $request
         * getShipWebId
         * author: walker
         * Date: 2020/1/3
         * Time: 15:14
         * Note:
         */
        public function getShipWebId(Request $request)
        {
            $shipList = DB::table('ship')
                          ->where(['webId' => 0, 'type' => 0])
                          ->limit(100)
                ->orderBy('dateWarehouseShipping','asc')
                          ->selectRaw('id,saleOrderCode')
                          ->get();
            $shipList = toArr($shipList);
            $model = new OrderInfo();
            $change = [];
            foreach ($shipList as $key => $val) {
                $change[] = $val['saleOrderCode'];
//                $info = $model->where(['source_id'=>$val['saleOrderCode']])->get();
//                $info = toArr($info);
//                if(!empty($info)){
//                    $web_id = $info->source_id;
//                    $change[]=$info;
////                    DB::table('ship')->where(['id'=>$val['id']])->update(['webId'=>$web_id]);
//                }
            }
           fp($shipList);
            ajaxReturn(200,'success',$change);
        }


    }
