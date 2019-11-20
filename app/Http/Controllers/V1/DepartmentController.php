<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Department;
    use Illuminate\Http\Request;

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
            ajaxReturn(200,Code::$com[200],$list);
        }

        /**
         * 修改部门
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
                                   'department_name'    => 'required|string|max:30',
                                   'department_manager' => 'required|string|max:30',
                                   'department_id'      => 'required|string',
                               ]);
            $data                       = [];
            $data['department_name']    = $request->department_name;
            $data['department_manager'] = $request->department_manager;
            $departmentId               = $request->department_id;
            $model                      = new Department();
            $result                     = $model->editStatus($departmentId, $data);
            if (empty($result)) ajaxReturn(4003, Code::$com[4003]);
            ajaxReturn(200, Code::$com[200]);
        }

        public function addDepartment(Request $request)
        {
            $request->validate([
                                   'department_name'    => 'required|string|max:30',
                                   'department_manager' => 'required|string|max:30',
                               ]);
            $data                       = [];
            $data['department_name']    = $request->department_name;
            $data['department_manager'] = $request->department_manager;
            $departmentId               = $request->department_id;
            $model                      = new Department();
            $result                     = $model->editStatus($departmentId, $data);
            if (empty($result)) ajaxReturn(4003, Code::$com[4003]);
            ajaxReturn(200, Code::$com[200]);
        }

    }
