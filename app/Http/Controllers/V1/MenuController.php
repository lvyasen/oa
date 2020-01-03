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
        public function getMenuList(Request $request)
        {
            $model           = new Menu();
            $userId = $request->user()->id;
            if(empty($userId)){
                ajaxReturn(4001,'未登录');
            }
            $where           = [];
            $where['is_del'] = 0;
            $menuList        = Menu::where($where)->orderBy('menu_id','asc')->get()->toArray();

            $treeList        = getMenuTree($menuList);
            ajaxReturn(200, Code::$com[200], $treeList);
        }

        /**
         * 获取用户菜单
         * @param Request $request
         * getUserMenuList
         * author: walker
         * Date: 2020/1/3
         * Time: 11:52
         * Note:
         */
        public function getUserMenuList(Request $request)
        {
            $model           = new Menu();
            $userId = $request->user()->id;
            if(empty($userId)){
                ajaxReturn(4001,'未登录');
            }
            $where           = [];
            $where['is_del'] = 0;
            $menuList        = Menu::where($where)->orderBy('menu_id','asc')->get()->toArray();
            $roleListArr = DB::table('user_role as ur')
                             ->leftJoin('role as r','ur.role_id','=','r.role_id')
                             ->where(['ur.user_id'=>$userId])
                             ->selectRaw('menu_list')
                             ->get();
            $roleListArr= toArr($roleListArr);
            $roleList = [];
            foreach ($roleListArr as $key => $val) {
                $roleList = array_merge($roleList,json_decode($val['menu_list'],true));
            }
            $roleList = array_unique($roleList);
            sort($roleList);


            foreach ($menuList as $key1 => $val1) {
                if(!in_array($val1['menu_id'],$roleList)){
                    unset($menuList[$key1]);
                };
            }
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
