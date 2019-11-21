<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Menu;
    use App\Models\V1\Operation;
    use App\Models\V1\Permission;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class PermissionController extends Controller
    {
        //
        /**
         * 添加权限
         *
         * @param Request $request
         * addPermission
         * author: walker
         * Date: 2019/11/21
         * Time: 11:10
         * Note:
         */
        public function addPermission(Request $request)
        {
            $request->validate([
                                   'permission_name' => 'required|string|max:30',
                                   'menu_id'         => 'required|string',
                                   'route'           => 'required|string|max:30|unique:permission',
                                   'permission_name' => 'required|string|max:60',
                                   'permission_type' => 'required|string',
                               ]);
            $model                  = new Permission();
            $model->menu_id         = $request->menu_id;
            $model->permission_name = $request->permission_name;
            $model->route           = $request->route;
            $model->permission_type = $request->permission_type;
            $info                   = $model->save();
            if (empty($info)) ajaxReturn(4002, Code::$com[4002]);
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改权限
         *
         * @param Request $request
         * editPermission
         * author: walker
         * Date: 2019/11/21
         * Time: 17:04
         * Note:
         */
        public function editPermission(Request $request)
        {
            $request->validate([
                                   'permission_id'   => 'required|string',
                                   'permission_name' => 'required|string',
                                   'menu_id'         => 'required|string',
                                   'permission_type' => 'required|string',
                               ]);
            $model                  = Permission::find($request->permission_id);
            $model->menu_id         = $request->menu_id;
            $model->permission_name   = $request->permission_name;
            $model->route           = $request->route;
            $model->permission_type = $request->permission_type;

            $info                   = $model->save();
            if (empty($info)) ajaxReturn(4002, Code::$com[4002]);
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改权限状态
         *
         * @param Request $request
         * editPermissionStatus
         * author: walker
         * Date: 2019/11/21
         * Time: 17:08
         * Note:
         */
        public function editPermissionStatus(Request $request)
        {
            $request->validate([
                                   'permission_id' => 'required|int',
                               ]);
            $model = new Permission();
            $res   = $model->editStatus($request->permission_id);
            if (empty($res)) ajaxReturn(4003, Code::$com[4003]);
            SystemController::sysLog($request, '修改权限');
            ajaxReturn(200, Code::$com[200]);
        }

        public function getPermissionList(Request $request)
        {

        }
    }
