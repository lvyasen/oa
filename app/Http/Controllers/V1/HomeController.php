<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class HomeController extends Controller
    {
        //
        private static $timeDiff = 43200;

        public function homeIndex(Request $request)
        {
            $start       = $request->start_time ? strtotime($request->start_time) : strtotime("Y-m-d");
            $end         = $request->end_time ? strtotime($request->end_time) : time();
            $topProducts = DB::table('shopify_order_line_item')
                             ->select(DB::raw("count(order_goods_id) as sale_times,name"))
                             ->whereBetween('created_at', [$start, $end])
                             ->orderBy('sale_times', 'desc')
                             ->groupBy('name')
                             ->limit(10)
                             ->get();
            $topProducts = toArr($topProducts);


            //总销售额
            $totalSales      = DB::table('shopify_order')
                                 ->whereBetween('created_at', [$start, $end])
                                 ->select(DB::raw("FROM_UNIXTIME(created_at,'%H') as add_time,sum(total_price) as total_sale"))
                                 ->groupBy(DB::raw("FROM_UNIXTIME(created_at,'%H')"))
                                 ->orderBy('add_time', 'asc')
                                 ->get();
            $totalSales      = toArr($totalSales);
            $countTotalSales = 0;
            foreach ($totalSales as $key => $val) {
                $countTotalSales += $val['total_sale'];
            }
            //总订单数
            $totalOrders      = DB::table('shopify_order')
                                  ->whereBetween('created_at', [$start, $end])
                                  ->select(DB::raw("FROM_UNIXTIME(created_at,'%H') as add_time,count(id) as total_order"))
                                  ->groupBy(DB::raw("FROM_UNIXTIME(created_at,'%H')"))
                                  ->orderBy('add_time', 'asc')
                                  ->get();
            $totalOrders      = toArr($totalOrders);
            $countTotalOrders = 0;
            foreach ($totalOrders as $key1 => $val1) {
                $countTotalOrders += $val1['total_order'];
            }


            $data                        = [];
            $data['totalSales']['list']  = $totalSales;
            $data['totalSales']['count'] = $countTotalSales;

            $data['totalOrders']['list']  = $totalOrders;
            $data['totalOrders']['count'] = $countTotalOrders;
            $data['top_products']         = $topProducts;

            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         *
         * @param Request $request
         * salesCount
         * author: walker
         * Date: 2020/1/4
         * Time: 16:12
         * Note:
         */
        public function salesCount(Request $request)
        {
            $start = date('Y-m-1', time());
            $end   = date('Y-m-d', time());

            $totalPrice = DB::table('shopify_order')
                            ->select(DB::raw("DATE_FORMAT(shopify_order.create_time,'%Y-%m-%d') as add_time,sum(shopify_order.total_price_usd) as total_sale,count(shopify_order.id) as order_nums,count(distinct email) as order_users"))
                            ->groupBy(DB::raw("DATE_FORMAT(shopify_order.create_time,'%Y-%m-%d')"))
                            ->orderBy('add_time', 'desc')
                            ->whereBetween('shopify_order.create_time', [$start, $end])
                            ->get();
            $totalPrice = toArr($totalPrice);
            $orderGoods = DB::table('shopify_order_line_item')
                            ->select(DB::raw("FROM_UNIXTIME(created_at,'%Y-%m-%d') as add_time,count(order_goods_id) as order_goods_nums"))
                            ->orderBy('add_time', 'desc')
                            ->groupBy(DB::raw("FROM_UNIXTIME(created_at,'%Y-%m-%d')"))
                            ->whereBetween('created_at', [strtotime($start), strtotime($end)])
                            ->get();
            $orderGoods =toArr($orderGoods);
            foreach ($orderGoods as $key => $val) {
                $totalPrice[$key]['order_goods_nums'] = $val['order_goods_nums'];
                $totalPrice[$key]['pct'] = round($totalPrice[$key]['total_sale']/$totalPrice[$key]['order_users'],2);
            }
            if(!empty($request->download)){

            }
            ajaxReturn(200,'成功',$totalPrice);

        }
    }
