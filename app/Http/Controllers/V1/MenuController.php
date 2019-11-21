<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Menu;
    use Illuminate\Http\Request;

    class MenuController extends BaseController
    {
        //
        /**
         * 添加菜单
         * @param Request $request
         * addMenu
         * author: walker
         * Date: 2019/11/20
         * Time: 11:41
         * Note:
         */
        public function addMenu(Request $request)
        {
            $user=$request->user();
            $request->validate([
                                   'menu_name' => 'required|string|max:30|unique:menu',
                                   'pid'       => 'required|string',
                                   'sort'      => 'string',
                                   'icon'      => 'string',
                                   'url'       => 'string',
                               ]);

            $model            = new Menu();
            $model->menu_name = $request->menu_name;
            $model->pid       = $request->pid;
            $model->sort      = $request->sort;
            $model->icon      = $request->icon;
            $model->url       = $request->url;
            $result           = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取菜单列表
         * getMenuList
         * author: walker
         * Date: 2019/11/20
         * Time: 11:43
         * Note:
         */
        public function getMenuList()
        {
            $model = new Menu();
            $where = [];
//            $where['']
        }
    }
