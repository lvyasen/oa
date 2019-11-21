<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class UsersController extends Controller
    {
        //
        /**
         * 获取用户列表
         * @param Request $request
         * getUserList
         * author: walker
         * Date: 2019/11/20
         * Time: 18:46
         * Note:
         */
        public function getUserList(Request $request)
        {
//            $userName = $request->user_name;
            $order    = $request->order;
            $page     = $request->page ?: 1;
            $pageNum  = $request->pageNum ?: 20;
            $mobile   = $request->mobile;
            $userName   = $request->user_name;

            $model = new User();
            $list  = $model->getUserList($userName,$mobile, $order);
            ajaxReturn(200, Code::$com[200], $list);
        }
    }
