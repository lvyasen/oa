<?php

namespace App\Http\Controllers\V1;

use App\Dictionary\Code;
use App\Http\Controllers\Controller;
use App\Models\V1\JobNeeds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    //
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
