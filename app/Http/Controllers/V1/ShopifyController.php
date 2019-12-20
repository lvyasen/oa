<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\Erp\ShopifyApi;
    use App\Models\V1\ShopifyAuth;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class ShopifyController extends Controller
    {
        private static $timeDiff = 43200;

        /**
         * 添加
         *
         * @param Request $request
         * addShopifyAuth
         * author: walker
         * Date: 2019/12/7
         * Time: 11:35
         * Note:
         */
        public function addShopifyAuth(Request $request)
        {
            $request->validate([
                                   'shop'   => 'required|string|max:30|unique:shopify_auth',
                                   'web_id' => 'required|string|max:30|unique:shopify_auth',
                               ]);

            $model         = new ShopifyAuth();
            $model->shop   = $request->shop;
            $model->web_id = $request->web_id;
            $result        = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加shopify站点');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * shopify站点列表
         *
         * @param Request $request
         * getShopifyAuthList
         * author: walker
         * Date: 2019/12/7
         * Time: 11:48
         * Note:
         */
        public function getShopifyAuthList(Request $request)
        {

            $page      = (int)$request->page ?: 1;
            $pageNum   = $request->pageNum ?: 10;
            $pageStart = ($page - 1) * $pageNum;
            $where     = [];
            if ( !empty($request->status)){
                $where['status'] = $request->status;
            }
            $table         = DB::table('shopify_auth');
            $list          = $table->where($where)->offset($pageStart)->limit($pageNum)->get();
            $count         = $table->where($where)->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 统计shopify订单
         * @param Request $request
         * countShopifyOrders
         * author: walker
         * Date: 2019/12/19
         * Time: 17:50
         * Note:
         */
        public function countShopifyOrders(Request $request)
        {
            $request->validate([
                                   'start_time' => 'required|date',
                                   'end_time'   => 'required|date',
                               ]);

            $start = strtotime($request->start_time);
            $end   = strtotime($request->end_time);
            $where = [];
            $where['confirmed']=1;
            if(!empty($request->web_id))$where['web_id'] = $request->web_id;
//            fp($start);
            $table = DB::table('shopify_order');
            $table->whereBetween('created_at',[$start,$end]);
//            $orderList = $table->where($where)->get();
            $count = $table->where($where)->count('id');
            $data = [];
            $data['count'] = $count;
            ajaxReturn(200,Code::$com[200],$data);
        }

        /**
         * 统计当天订单量
         * @param Request $request
         * countShopifyTodayOrders
         * author: walker
         * Date: 2019/12/20
         * Time: 14:00
         * Note:
         */
        public function countShopifyTodayOrders(Request $request)
        {
            $start = date('Y-m-d H:i:s');
            $end   =time();

            fp(date("Y-m-d H:i:s",strtotime('2008-01-10T11:00:00-05:00')));
//            $model = new ShopifyApi();
//            $info = $model->countOrder($start,$end);
//            fp($info);
            $where = [];
            $where['confirmed']=1;
            $table = DB::table('shopify_order');
//            $table->whereBetween('updated_at',[$start,$end]);
            $count = $table->where('updated_at','>',$start)->count('id');
            $data = [];
            $data['count'] = $count;
            ajaxReturn(200,Code::$com[200],$data);
        }

    }
