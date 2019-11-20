<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\User;
    use Illuminate\Http\Request;

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
            $where    = [];

            if ( !empty($mobile)) $where['mobile'] = $mobile;
            
            $model = new User();
            $list  = $model->getUserList($where, $order);
            ajaxReturn(200, Code::$com[200], $list);
        }
    }
