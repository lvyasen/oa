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
            $page        = (int)$request->page ?: 1;
            $pageNum     = $request->pageNum ?: 10;
            $pageStart   = ($page - 1) * $pageNum;
            $start = $request->start_time ? strtotime($request->start_time) : strtotime("-30 day");
            $end   = $request->end_time ? strtotime($request->end_time) : time();
            $where = [];
            if ( !empty($request->web_id))$where['web_id'] = $request->web_id;
            $field = "";
            $table = DB::table('e_order_goods_cost');
            $list = $table->where($where)
                ->whereBetween('pay_time',[date("Y-m-d H:i:s",$start),date("Y-m-d H:i:s",$end)])
//                ->selectRaw($field)
                ->offset($pageStart)
                ->limit($pageNum)
                ->get();
            $count = $table->where($where)
                          ->whereBetween('pay_time',[date("Y-m-d H:i:s",$start),date("Y-m-d H:i:s",$end)])
                          ->count();
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200,Code::$com[200],$data);

        }
    }
