<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Menu;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class MenuController extends BaseController
    {
        //
        /**
         * 添加菜单
         *
         * @param Request $request
         * addMenu
         * author: walker
         * Date: 2019/11/20
         * Time: 11:41
         * Note:
         */
        public function addMenu(Request $request)
        {

            $request->validate([
                                   'menu_name' => 'required|string|max:30|unique:menu',
                                   'pid'       => 'required|string',
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
            SystemController::sysLog($request, '添加菜单');
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
            $model           = new Menu();
            $where           = [];
            $where['is_del'] = 0;
            $menuList        = Menu::where($where)->get()->toArray();
            $treeList        = getMenuTree($menuList);
            ajaxReturn(200, Code::$com[200], $treeList);
        }


        /**
         * 修改菜单状态
         *
         * @param Request $request
         * editMenuStatus
         * author: walker
         * Date: 2019/11/21
         * Time: 16:47
         * Note:
         */
        public function editMenuStatus(Request $request)
        {
            $request->validate([
                                   'menu_id' => 'required|string',
                               ]);
            $model = new Menu();
            $res   = $model->editStatus($request->menu_id);
            if (empty($res)) ajaxReturn(4003, Code::$com[4003]);
            SystemController::sysLog($request, '修改菜单');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改菜单
         *
         * @param Request $request
         * editMenu
         * author: walker
         * Date: 2019/11/23
         * Time: 11:07
         * Note:
         */
        public function editMenu(Request $request)
        {
            $request->validate([
                                   'menu_id'   => 'required|string',
                                   'icon'      => 'required|string',
                                   'url'       => 'required|string',
                                   'menu_name' => 'required|string',
                                   'status' => 'required|string',
                               ]);

            $model            = Menu::find($request->menu_id);
            $model->pid       = $request->pid;
            $model->menu_name = $request->menu_name;
            $model->sort      = $request->sort;
            $model->icon      = $request->icon;
            $model->status      = $request->status;
            $model->url       = $request->url;
            $result           = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改菜单信息');
            ajaxReturn(200, Code::$com[200]);
        }

    }
