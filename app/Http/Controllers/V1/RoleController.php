<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Role;
    use Illuminate\Http\Request;

    class RoleController extends Controller
    {
        /**
         * 新增角色
         * addRole
         * author: walker
         * Date: 2019/11/21
         * Time: 9:46
         * Note:
         */
        public function addRole(Request $request)
        {
            $request->validate([
                                   'role_name' => 'required|string|max:30|unique:role',
                               ]);
            $model            = new Role();
            $model->role_name = $request->role_name;
            $model->status    = 1;
            $res = $model->save();
            if(empty($res)) ajaxReturn(4002,Code::$com[4002]);
            ajaxReturn(200,Code::$com[200]);
        }

        /**
         * 删除角色
         * @param Request $request
         * delRole
         * author: walker
         * Date: 2019/11/21
         * Time: 9:56
         * Note:
         */
        public function delRole(Request $request)
        {
            $request->validate([
                                   'role_id' => 'required|string',
                               ]);
            $where = [];
            $where['role_id'] = $request->role_id;
//            Role::where($where)->
        }
    }
