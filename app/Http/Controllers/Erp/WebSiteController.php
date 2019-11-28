<?php

    namespace App\Http\Controllers\Erp;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\Erp\ShopifyApi;
    use App\Models\Erp\SiteWeb;
    use Illuminate\Http\Request;
    use PHPShopify\ShopifySDK;

    class WebSiteController extends Controller
    {
        //
        /**
         * 获取Erp shopify信息
         *
         * @param Request $request
         * getShopifyWebsite
         * author: walker
         * Date: 2019/11/27
         * Time: 13:44
         * Note:
         */
        public function getShopifyWebsite(Request $request)
        {
            $config = array(
                'ShopUrl' => 'yourshop.myshopify.com',
                'AccessToken' => '***ACCESS-TOKEN-FOR-THIRD-PARTY-APP***',
            );

            $page          = (int)$request->page ?: 1;
            $pageNum       = $request->pageNum ?: 10;
            $pageStart     = ($page - 1) * $pageNum;
            $where         = [];
            $startTime     = $request->start_time ? strtotime($request->start_time) : 0;
            $endTime       = $request->end_time ? strtotime($request->end) : time();
            $table         = new SiteWeb();
            $list          = $table->where($where)->offset($pageStart)->limit($pageNum)->get();
            $count         = $table->where($where)->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }
    }
