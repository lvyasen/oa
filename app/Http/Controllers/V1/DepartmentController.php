<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Department;
    use App\Models\V1\System;
    use App\Models\V1\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    /**
     * 部门控制器
     * Class DepartmentController
     * User: walker
     * Date: 2019/11/19
     * Time: 18:07
     * Caret:
     *
     * @package App\Http\Controllers\V1
     */
    class DepartmentController extends Controller
    {
        //
        /**
         * 获取部门列表
         * getDepartmentList
         * author: walker
         * Date: 2019/11/19
         * Time: 18:08
         * Note:
         */
        public function getDepartmentList()
        {
            $model = new Department();
            $list  = $model->getDepartmentList();
            if ( !empty($list)){
                foreach ($list as $key => $val) {
                    $where                        = [
                        'department_id' => $val['department_id'],
                    ];
                    $list[$key]['department_num'] = DB::table('users')->where($where)->count('id');
                }
            }
            $tree         = getDepartmentTree($list);
            $data         = [];
            $data['list'] = $tree;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 获取所有部门列表
         * getDepartmentAllList
         * author: walker
         * Date: 2019/11/22
         * Time: 17:38
         * Note:
         */
        public function getDepartmentAllList()
        {
            $model        = new Department();
            $list         = $model->getDepartmentList();
            $data         = [];
            $data['list'] = $list;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 修改部门
         *
         * @param Request $request
         * editDepartment
         * author: walker
         * Date: 2019/11/20
         * Time: 10:30
         * Note:
         */
        public function editDepartment(Request $request)
        {
            $request->validate([
                                   'department_name' => 'required|string|max:30|unique:department',
                                   'desc'            => 'required|string|max:255',
                                   'department_id'   => 'required|string',
                                   'manager_user_id' => 'required|string',
                                   'pid'             => 'required|string',
                               ]);
            $data                       = [];
            $data['department_name']    = $request->department_name;
            $data['department_manager'] = User::getUserInfo($request->manager_user_id)->name;
            $data['pid']                = $request->pid;
            $data['desc']               = $request->desc;
            $data['manager_user_id']    = $request->manager_user_id;
            $departmentId               = $request->department_id;
            $model                      = new Department();

            $result = $model->editDepartment($departmentId, $data);
            if (empty($result)) ajaxReturn(4003, Code::$com[4003]);
            SystemController::sysLog($request, '修改部门');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改部门状态
         *
         * @param Request $request
         * editDepartmentStatus
         * author: walker
         * Date: 2019/11/22
         * Time: 15:31
         * Note:
         */
        public function editDepartmentStatus(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|string',
                               ]);

            $departmentId = $request->department_id;
            $model        = new Department();
            $result       = $model->editStatus($departmentId);
            if (empty($result)) ajaxReturn(4003, Code::$com[4003]);
            SystemController::sysLog($request, '修改部门状态');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 添加部门
         *
         * @param Request $request
         * addDepartment
         * author: walker
         * Date: 2019/11/20
         * Time: 17:52
         * Note:
         */
        public function addDepartment(Request $request)
        {

            $request->validate([
                                   'department_name' => 'required|string|max:30',
                                   'manager_user_id' => 'required|string',
                                   'desc'            => 'required|string|max:255',
                                   'pid'             => 'required|string',
                               ]);
            $model                     = new Department();
            $model->department_name    = $request->department_name;
            $model->manager_user_id    = $request->manager_user_id;
            $model->department_manager = User::getUserInfo($request->manager_user_id)->name;
            $model->desc               = $request->desc;
            $model->pid                = $request->pid;
            $model->status             = 1;
            $result                    = $model->save();
            if (empty($result)) ajaxReturn(4003, Code::$com[4003]);
            SystemController::sysLog($request, '添加部门');
            ajaxReturn(200, Code::$com[200]);

        }

        /**
         * 删除部门
         *
         * @param Request $request
         * delDepartment
         * author: walker
         * Date: 2019/11/20
         * Time: 18:10
         * Note:
         */
        public function delDepartment(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|string',
                               ]);
            $model = new Department();
            $res   = $model->where(['department_id' => $request->department_id])->delete();
            if (empty($res)) ajaxReturn(4004, Code::$com[4004]);
            SystemController::sysLog($request, '删除部门');
            ajaxReturn(200, Code::$com[200]);
        }


    }
