<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\ShopifyAuth;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class ShopifyController extends Controller
    {
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
    }
