<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use function GuzzleHttp\Psr7\str;

    class OrderCostController extends Controller
    {
        /**
         * 获取采购列表数据
         *
         * @param Request $request
         * getOrderCostList
         * author: walker
         * Date: 2019/12/24
         * Time: 10:39
         * Note:
         */
        public function getOrderCostList(Request $request)
        {

            $request->validate([
                                   'start_time' => 'nullable|date',
                                   'end_time'   => 'nullable|date',
                                   //                                   'web_id'   => 'nullable|string|exists:connection.erp.siteweb',
                               ]);
            $page      = (int)$request->page ?: 1;
            $pageNum   = $request->pageNum ?: 10;
            $pageStart = ($page - 1) * $pageNum;
            $start     = $request->start_time ? strtotime($request->start_time) : strtotime("-30 day");
            $end       = $request->end_time ? strtotime($request->end_time) : time();
            $where     = [];
            if ( !empty($request->web_id)) $where['web_id'] = $request->web_id;
            $field         = "";
            $table         = DB::table('e_order_goods_cost');
            $list          = $table->where($where)
                                   ->whereBetween('pay_time', [date("Y-m-d H:i:s", $start), date("Y-m-d H:i:s", $end)])
                //                ->selectRaw($field)
                                   ->orderBy('pay_time', 'desc')
                                   ->offset($pageStart)
                                   ->limit($pageNum)
                                   ->get();
            $count         = DB::table('e_order_goods_cost')
                               ->where($where)
                               ->whereBetween('pay_time', [date("Y-m-d H:i:s", $start), date("Y-m-d H:i:s", $end)])
                               ->count('id');
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);

        }

        /**
         * 采购费用统计图
         *
         * @param Request $request
         * getOrderCostChart
         * author: walker
         * Date: 2019/12/27
         * Time: 18:25
         * Note:
         */
        public function getOrderCostChart(Request $request)
        {
            $where = [];
            if ( !empty($request->web_id)) $where['web_id'] = $request->web_id;
            $orderList = DB::table('e_order_goods_cost')
                           ->where($where)
                           ->select(DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as pay_time,sum(totalCost) as total_price"))
                           ->groupBy(DB::raw("DATE_FORMAT(pay_time,'%Y-%m')"))
                           ->orderBy('pay_time', 'desc')
                //                           ->selectRaw('totalCost,pay_time,web_id')
                           ->get();
            $orderList = toArr($orderList);
            $orderCost = [];
            foreach ($orderList as $key1 => $val1) {
                $orderCost[$val1['pay_time']] = $val1['total_price'];
            }
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
                $data['order_cost'][$key]['date'] = $val;
                if (array_key_exists($val, $orderCost)){
                    $data['order_cost'][$key]['total_price'] = $orderCost[$val];
                } else {
                    $data['order_cost'][$key]['total_price'] = 0;
                };
            }

            ajaxReturn(200, Code::$com[200], $data);
        }
    }
