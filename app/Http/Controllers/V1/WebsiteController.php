<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Website;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class WebsiteController extends Controller
    {
        //
        /**
         * 添加站点
         *
         * @param Request $request
         * addWebsite
         * author: walker
         * Date: 2019/11/22
         * Time: 18:05
         * Note:
         */
        public function addWebsite(Request $request)
        {
            $request->validate([
                                   'website_name' => 'required|string|max:60|unique:website',
                                   'website_api'  => 'required|string|max:255|unique:website|active_url',
                                   'website_type' => 'required|string',
                               ]);

            $model               = new Website();
            $model->website_name = $request->website_name;
            $model->website_api  = $request->website_api;
            $model->website_type = $request->website_type;
            $result              = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改站点
         *
         * @param Request $request
         * editWebsite
         * author: walker
         * Date: 2019/11/22
         * Time: 18:07
         * Note:
         */
        public function editWebsite(Request $request)
        {
            $request->validate([
                                   'website_api'  => 'required|string|active_url',
                                   'website_name' => 'required|string',
                                   'website_type' => 'required|string',
                                   'website_id'   => 'required|string',
                               ]);

            $model               = Website::find($request->website_id);
            $model->website_api  = $request->website_api;
            $model->website_name = $request->website_name;
            $model->website_type = $request->website_type;
            $result              = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改站点信息');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取站点列表
         *
         * @param Request $request
         * getWebsiteList
         * author: walker
         * Date: 2019/11/22
         * Time: 18:12
         * Note:
         */
        public function getWebsiteList(Request $request)
        {
            $page            = (int)$request->page ?: 1;
            $pageNum         = $request->pageNum ?: 10;
            $pageStart       = ($page - 1) * $pageNum;
            $websiteName     = $request->website_name;
            $where           = [];
            $where['is_del'] = 0;
            $table           = DB::table('website');
            if ( !empty($websiteName)) $table->where('website_name', 'like', "%" . $websiteName . "%");
            $list          = $table->where($where)->offset($pageStart)->limit($pageNum)->get();
            $count         = $table->where($where)->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 删除站点
         *
         * @param Request $request
         * delWebsite
         * author: walker
         * Date: 2019/11/22
         * Time: 18:16
         * Note:
         */
        public function delWebsite(Request $request)
        {
            $request->validate([
                                   'website_id' => 'required|string|exists:website',
                               ]);
            $model         = Website::find($request->website_id);
            $model->is_del = 1;
            $result        = $model->save();
            if (empty($result)) ajaxReturn(4004, Code::$com[4004]);
            SystemController::sysLog($request, '删除站点');
            ajaxReturn(200, Code::$com[200]);
        }
    }
