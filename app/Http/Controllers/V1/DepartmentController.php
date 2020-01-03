<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Imports\PersonnelImport;
    use App\Models\V1\Department;
    use App\Models\V1\JobNeeds;
    use App\Models\V1\System;
    use App\Models\V1\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;
    use Maatwebsite\Excel\Facades\Excel;

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
                                   'department_name' => 'required|string|max:30',
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
            $result                     = $model->editDepartment($departmentId, $data);
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

        /**
         * 获取部门用户列表
         *
         * @param Request $request
         * getDepartmentUsers
         * author: walker
         * Date: 2019/11/23
         * Time: 17:22
         * Note:
         */
        public function getDepartmentUsers(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|string|exists:department',
                               ]);
            $page            = (int)$request->page ?: 1;
            $pageNum         = $request->pageNum ?: 10;
            $pageStart       = ($page - 1) * $pageNum;
            $departmentId    = $request->department_id;
            $where           = [];
            $where['status'] = 1;
            if ( !empty($departmentId)) $where['department_id'] = $departmentId;
            $table         = DB::table('users');
            $list          = $table->where($where)->offset($pageStart)->limit($pageNum)->get();
            $count         = $table->where($where)->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /***
         *###########################    人事部门相关    ###########################
         *                 .-~~~~~~~~~-._       _.-~~~~~~~~~-.
         *             __.'              ~.   .~              `.__
         *           .'//                  \./                  \\`.
         *         .'//                     |                     \\`.
         *       .'// .-~"""""""~~~~-._     |     _,-~~~~"""""""~-. \\`.
         *     .'//.-"                 `-.  |  .-'                 "-.\\`.
         *   .'//______.============-..   \ | /   ..-============.______\\`.
         * .'______________________________\|/______________________________`.
         *
         */
        /**
         * 添加职位需求
         *
         * @param Request $request
         * addJobNeeds
         * author: walker
         * Date: 2020/1/2
         * Time: 17:14
         * Note:
         */
        public function addJobNeeds(Request $request)
        {
            $request->validate([
                                   'job_name'      => 'required|string|max:100|unique:job_needs',
                                   'department_id' => 'required|string|exists:department',
                                   'people'        => 'required|string',
                                   'desc'          => 'nullable|string',
                                   'task_user'     => 'required|string',
                                   'start_time'    => 'required|date',
                                   'end_time'      => 'required|date',
                               ]);
            $userId          = $request->user()->id;
            $manager_user_id = DB::table('department')
                                 ->where(['department_id' => $request->department_id])
                                 ->first('manager_user_id');
            $peopleManagerId = DB::table('department')
                                 ->where(['manager_user_id' => $userId])
                                 ->first('manager_user_id');
            if (empty($peopleManagerId)){
                ajaxReturn(4001, '只有部门负责人才能发布需求');
            }
            $model                  = new JobNeeds();
            $model->job_name        = $request->job_name;
            $model->department_id   = $request->department_id;
            $model->people          = $request->people;
            $model->start_time      = $request->start_time;
            $model->end_time        = $request->end_time;
            $model->manager_user_id = $manager_user_id;
            $model->user_id         = $userId;
            $model->task_user       = implode(',', json_decode($request->task_user, true));
            $model->manager_user_id = $manager_user_id->manager_user_id;
            $model->status          = 1;
            $model->complete        = 0;

            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加职位需求成功');
            ajaxReturn(200, Code::$com[200]);

        }

        /**
         * 修改职位
         *
         * @param Request $request
         * editJobNeeds
         * author: walker
         * Date: 2020/1/2
         * Time: 18:27
         * Note:
         */
        public function editJobNeeds(Request $request)
        {
            $request->validate([
                                   'job_needs_id'  => 'required|string|exists:job_needs,id',
                                   'department_id' => 'required|string|exists:department',
                               ]);

            $model                = JobNeeds::find($request->job_needs_id);
            $model->job_name      = $request->job_name;
            $model->department_id = $request->department_id;
            $model->people        = $request->people;
            if ( !empty($request->department_id)){
                $manager_user_id        = DB::table('department')
                                            ->where(['department_id' => $request->department_id])
                                            ->first('manager_user_id');
                $model->manager_user_id = $manager_user_id->manager_user_id;
            }

            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改职位需求信息');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取需求职位信息
         *
         * @param Request $request
         * getJobNeedsInfo
         * author: walker
         * Date: 2020/1/3
         * Time: 10:59
         * Note:
         */
        public function getJobNeedsInfo(Request $request)
        {
            $request->validate([
                                   'job_needs_id' => 'required|string|exists:job_needs,id',
                               ]);
            $model = JobNeeds::find($request->job_needs_id);
            $info  = $model->get()->toArray();
            ajaxReturn(200, '成功', $info);
        }

        /**
         * 获取人事指派人员列表
         *
         * @param Request $request
         * getTaskUser
         * author: walker
         * Date: 2020/1/3
         * Time: 11:07
         * Note:
         */
        public function getTaskUser(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|string|exists:department',
                               ]);
            //            $manageUserId = DB::table('department')
            //                              ->where(['department_id' => $request->department_id])
            //                              ->first('manager_user_id');
            $userList     = DB::table('users')
                              ->where(['department_id' => $request->department_id, 'status' => 1])
                              ->selectRaw('name,id,status')
                              ->get();
            $data['list'] = $userList;
            ajaxReturn(200, '成功', $data);
        }

        /**
         * 删除职位需求信息
         *
         * @param Request $request
         * delJobNeeds
         * author: walker
         * Date: 2020/1/3
         * Time: 17:00
         * Note:
         */
        public function delJobNeeds(Request $request)
        {
            $request->validate([
                                   'job_needs_id' => 'required|string|exists:job_needs,id',
                               ]);

            $model            = JobNeeds::find($request->job_needs_id);
            $model->is_delete = 1;
            $result           = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '删除职位需求信息');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取任务列表
         *
         * @param Request $request
         * getJobNeedsList
         * author: walker
         * Date: 2020/1/3
         * Time: 17:09
         * Note:
         */
        public function getJobNeedsList(Request $request)
        {
            $request->validate([
                                   'type' => 'required|string',
                               ]);
            $type      = $request->type;
            $page      = (int)$request->page ?: 1;
            $pageNum   = $request->pageNum ?: 10;
            $pageStart = ($page - 1) * $pageNum;
            $userId    = $request->user()->id;
            $where     = [];
            $startTime = $request->start_time ? strtotime($request->start_time) : 0;
            $endTime   = $request->end_time ? strtotime($request->end) : time();
            if ( !empty($request->complete)) $where['complete'] = $request->complete;
            if ( !empty($request->department_id)) $where['department_id'] = $request->department_id;
            $where['is_delete'] = 0;
            $table              = DB::table('job_needs');
            $table->whereBetween('add_time', [date("Y-m-d H:i:s", $startTime), date("Y-m-d H:i:s", $endTime)]);
            if ($type == '1'){
                $list  = $table
                    ->where($where)
                    ->offset($pageStart)
                    ->limit($pageNum)
                    ->get();
                $count = $table->where($where)->count();
            } elseif ($type == '2') {
                $list  = $table
                    ->where($where)
                    ->where('task_user', 'like', '%' . $userId . '%')
                    ->offset($pageStart)
                    ->limit($pageNum)
                    ->get();
                $count = $table->where($where)->where('task_user', 'like', '%' . $userId . '%')->count();
            };
            if ( !empty($list)){
                $list = toArr($list);
                foreach ($list as $key => $val) {
                    if(!empty($val['task_user'])){
                        $users = DB::table('users')->whereIn('id',explode(',',$val['task_user']))->selectRaw('name')->get();
                        $users = toArr($users);
                        $list[$key]['task_user_name']=$users;
                    }else{
                        $list[$key]['task_user_name']=null;
                    }
//                    $list['task_user_name']
                }
            }
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }


    }
