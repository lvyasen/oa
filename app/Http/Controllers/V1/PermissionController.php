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
                                   'permission_type' => 'required|string',
                               ]);
            $model = new Permission();
            $model->menu_id = $request->menu_id;
            $model->route = $request->route;
            $model->permission_type = $request->permission_type;
            $info = $model->save();
            if(empty($info)) ajaxReturn(4002,Code::$com[4002]);
            ajaxReturn(200,Code::$com[200]);
        }

    }
