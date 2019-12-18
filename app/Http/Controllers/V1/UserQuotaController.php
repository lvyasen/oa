<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Department;
    use App\Models\V1\User;
    use App\Models\V1\UserQuota;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class UserQuotaController extends Controller
    {
        //
        /**
         * 添加用户指标
         *
         * @param Request $request
         * addUserQuota
         * author: walker
         * Date: 2019/11/23
         * Time: 16:34
         * Note:
         */
        public function addUserQuota(Request $request)
        {
            $request->validate([
                                   'user_id'          => 'required|int|exists:users,id',
                                   'target_value'     => 'required|int',
                                   'target_date_type' => 'required|int',
                                   //                                   'complete_value'   => 'required|int',
                                   //                                   'score'            => 'required|int',
                                   'complete_date'    => 'required|date',
                                   'unit'             => 'required|int',
                                   'quota_id'         => 'required|int|exists:quota',
                                   'department_id'    => 'required|int|exists:department',
                               ]);

            $model                   = new UserQuota();
            $model->user_id          = $request->user_id;
            $model->user_name        = User::getUserInfo($request->user_id, 'name')->name;
            $model->target_value     = $request->target_value;
            $model->target_date_type = $request->target_date_type;
            $model->complete_value   = $request->complete_value;
            $model->score            = $request->score;
            $model->complete_date    = strtotime($request->complete_date);
            $model->unit             = $request->unit;
            $model->quota_id         = $request->quota_id;
            $model->department_id    = $request->department_id;
            $model->remark           = $request->remark;
            $result                  = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加用户指标');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改用户指标
         *
         * @param Request $request
         * editUserQuota
         * author: walker
         * Date: 2019/11/23
         * Time: 16:34
         * Note:
         */
        public function editUserQuota(Request $request)
        {
            $request->validate([
                                   'user_id'          => 'required|int|exists:users,id',
                                   'target_value'     => 'required|int',
                                   'target_date_type' => 'required|int',
                                   'complete_value'   => 'required|int',
                                   'score'            => 'required|int',
                                   'complete_date'    => 'required|date',
                                   'unit'             => 'required|int',
                                   'quota_id'         => 'required|int|exists:quota',
                                   'department_id'    => 'required|int|exists:department',
                                   'user_quota_id'    => 'required|int|exists:user_quota',
                               ]);

            $model                   = UserQuota::find($request->user_quota_id);
            $model->user_id          = $request->user_id;
            $model->user_name        = User::getUserInfo($request->user_id, 'name')->name;
            $model->target_value     = $request->target_value;
            $model->target_date_type = $request->target_date_type;
            $model->complete_value   = $request->complete_value;
            $model->score            = $request->score;
            $model->complete_date    = strtotime($request->complete_date);
            $model->unit             = $request->unit;
            $model->quota_id         = $request->quota_id;
            $model->department_id    = $request->department_id;
            $model->remark           = $request->remark;
            if ( !empty($request->mobile)) $model->mobile = $request->mobile;
            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改用户指标');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取用户指标列表
         *
         * @param Request $request
         * getUserQuotaList
         * author: walker
         * Date: 2019/11/23
         * Time: 16:34
         * Note:
         */
        public function getUserQuotaList(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|string|exists:department',
                               ]);
            $page                       = (int)$request->page ?: 1;
            $pageNum                    = $request->pageNum ?: 10;
            $pageStart                  = ($page - 1) * $pageNum;
            $departmentId               = $request->department_id;
            $userId                     = $request->user_id;
            $where                      = [];
            $where['user_quota.is_del'] = 0;
            $startTime                  = $request->start_time ? strtotime($request->start_time) : 0;
            $endTime                    = $request->end_time ? strtotime($request->end_time) : time();
            if ( !empty($departmentId)) $where['user_quota.department_id'] = $departmentId;
            if ( !empty($userId)) $where['user_id'] = $userId;
            $table = DB::table('user_quota');
            $table->whereBetween('user_quota.add_time', [$startTime, $endTime]);
            $list          = $table
                ->selectRaw('user_quota.*,quota.quota_name,quota.quota_id')
                ->leftJoin('quota', 'user_quota.quota_id', '=', 'quota.quota_id')
                ->where($where)
                ->offset($pageStart)
                ->limit($pageNum)
                ->get();
            $count         = $table->where($where)->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 删除用户指标
         *
         * @param Request $request
         * delUserQuota
         * author: walker
         * Date: 2019/11/23
         * Time: 16:35
         * Note:
         */
        public function delUserQuota(Request $request)
        {
            $request->validate([
                                   'user_quota_id' => 'required|int|exists:user_quota',
                               ]);

            $model         = UserQuota::find($request->user_quota_id);
            $model->is_del = 1;
            $result        = $model->save();
            if (empty($result)) ajaxReturn(4004, Code::$com[4004]);
            SystemController::sysLog($request, '删除用户指标');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 部门负责人指标
         *
         * @param Request $request
         * getDepartmentQuotaList
         * author: walker
         * Date: 2019/12/4
         * Time: 13:51
         * Note:
         */
        public function getDepartmentQuotaList(Request $request)
        {

            $request->validate([
                                   'department_id' => 'nullable|Integer',
                                   'start_time'    => 'nullable|date',
                                   'end_time'      => 'nullable|date',
                               ]);
            $departmentId    = $request->department_id;
            $departmentWhere = [];
            if ( !empty($request->department_id)) $departmentWhere['department_id'] = $departmentId;

            $departmentList = DB::table('department as d')
                                ->where($departmentWhere)
                                ->get()->toArray();
            $startTime      = $request->start_time ? (int)strtotime($request->start_time) : 0;
            $endTime        = $request->start_time ? (int)strtotime($request->start_time) : time();

            $departmentList = toArr($departmentList);
            if ( !empty($departmentList)){
                foreach ($departmentList as $key => $val) {
                    $list = DB::table('quota')
                              ->where(['is_del' => 0, 'department_id' => $val['department_id']])
                              ->selectRaw('quota_id,quota_name,is_del')
                              ->get();
                    $list = toArr($list);
                    if ( !empty($list)){
                        foreach ($list as $key1 => $val1) {
                            $table                               = DB::table('user_quota');
                            $sumRes                              = $table
                                ->whereBetween('complete_date', [$startTime, $endTime])
                                ->where([
                                            'is_del'   => 0,
                                            'quota_id' => $val1['quota_id'],
                                        ])
                                ->first([
                                            DB::raw('SUM(target_value) as total_target_value'),
                                            DB::raw('SUM(complete_value) as total_complete_value'),
                                        ]);
                            $list[$key1]['total_target_value']   = $sumRes->total_target_value ?: 0;
                            $list[$key1]['total_complete_value'] = $sumRes->total_complete_value ?: 0;
                        }
                    }

                    $departmentList[$key]['quota_list'] = $list;
                }


            }
            //            dump($departmentList);
            ajaxReturn(200, Code::$com[200], $departmentList);

        }

        /**
         * 部门每日详情
         *
         * @param Request $request
         * getDepartmentQuotaDetail
         * author: walker
         * Date: 2019/12/4
         * Time: 17:33
         * Note:
         */
        public function getDepartmentQuotaDetail(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|Integer|exists:department',
                                   'start_time'    => 'nullable|date',
                                   'end_time'      => 'nullable|date',
                               ]);
            //根据部门查看每个人的指标详情

            $field                             = 'user_quota.department_id,user_quota.quota_id,quota.quota_id,complete_value,
            score,complete_date,user_name,quota_name,target_value,unit
            ';
            $where                             = [];
            $where['user_quota.department_id'] = $request->department_id;
            $table                             = DB::table('user_quota');
            $startTime                         = $request->start_time ?: strtotime('-1 year');
            $endTime                           = $request->end_time ?: time();
            $table->whereBetween('complete_date', [$startTime, $endTime]);
            $userQuotaList = $table
                ->leftJoin('quota', 'quota.quota_id', '=', 'user_quota.quota_id')
                ->orderBy('user_quota.quota_id', 'asc')
                ->selectRaw($field)
                ->where($where)
                ->get();
            $userQuotaList = toArr($userQuotaList);

            $year
                = date('Y', time());
            #一年的月份
            $month = [
                0  => $year . '-01',
                1  => $year . '-02',
                2  => $year . '-03',
                3  => $year . '-04',
                4  => $year . '-05',
                5  => $year . '-06',
                6  => $year . '-07',
                7  => $year . '-08',
                8  => $year . '-09',
                9  => $year . '-10',
                10 => $year . '-11',
                11 => $year . '-12',
            ];
            foreach ($month as $key => $val) {
                $quotaChart[$key] = [
                    'date'  => $val,
                    'value' => 0,
                ];
                foreach ($userQuotaList as $key1 => $val1) {
                    if ($val == \date('Y-m', $val1['complete_date'])){
                        $quotaChart[$key]['value'] = $quotaChart[$key]['value']+$val1['complete_value'];
                    };
                }
            }
            $data['list']  = $userQuotaList;
            $data['quota_chart']  = $quotaChart;
            ajaxReturn(200, Code::$com[200], $data);
        }


        /**
         * 部门指标数据
         *
         * @param Request $request
         * getDepartmentQuotaAnalytics
         * author: walker
         * Date: 2019/12/16
         * Time: 11:59
         * Note:
         */
        public function getDepartmentQuotaAnalytics(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|string|exists:department',
                                   'start_time'    => 'nullable|date',
                                   'end_time'      => 'nullable|date',
                                   'user_id'       => 'nullable|string|exists:users,id',
                               ]);

            $userId          = $request->user_id ?: $request->user()->id;
            $userName        = DB::table('users')->where(['id' => $userId])->first('name');
            $departmentModel = new Department();
            $departmentInfo  = $departmentModel->where(['manager_user_id' => $userId, 'department_id' => $request->department_id])->first('department_id');
            $startTime       = $request->start_time ?: strtotime('-1 month');
            $endTime         = $request->end_time ?: time();
            if (empty($departmentInfo)){
                ajaxReturn(4005, Code::$user['not_have_promise']);
            }
            $quotaList = DB::table('quota')->where(['department_id' => $departmentInfo->department_id])->get();
            $quotaList = toArr($quotaList);
            foreach ($quotaList as $key => $val) {
                $userQuota = DB::table('user_quota');
                $userQuota->whereBetween('complete_date', [$startTime, $endTime]);
                $userQuotaLists            = $userQuota
                    ->where([
                                'is_del'   => 0,
                                'quota_id' => $val['quota_id'],
                                'user_id'  => $userId,
                            ])
                    ->get();
                $userQuotaLists            = toArr($userQuotaLists);
                $quotaList[$key]['detail'] = $userQuotaLists;
            }
            $info              = [];
            $info['user_name'] = $userName;
            $info['list']      = $quotaList;
            ajaxReturn(200, Code::$com[200], $info);
        }

    }
