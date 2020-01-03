<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Quota;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class QuotaController extends Controller
    {
        //
        /**
         * 添加部门指标
         *
         * @param Request $request
         * addQuota
         * author: walker
         * Date: 2019/11/22
         * Time: 15:00
         * Note:
         */
        public function addQuota(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|string|exists:department,department_id',
                                   'quota_name'    => 'required|string',
                               ]);

            $departmentName         = DB::table('department')->where(['department_id' => $request->department_id])
                                        ->select('department_name')->first()->department_name;
            $model                  = new Quota();
            $model->department_id   = $request->department_id;
            $model->department_name = $departmentName;
            $model->quota_name      = $request->quota_name;
            $model->quota_desc      = $request->quota_desc;
            $model->quota_type      = 1;
            $model->weight          = $request->weight;
            $result                 = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加部门指标');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取部门所有指标权重
         *
         * @param Request $request
         * getLastWeight
         * author: walker
         * Date: 2020/1/2
         * Time: 13:58
         * Note:
         */
        public function getLastWeight(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|string|exists:department,department_id',
                               ]);
            $where                  = [];
            $where['department_id'] = $request->department_id;
            $where['is_del']        = 0;
            $totalWeight            = DB::table('quota')
                                        ->where($where)
                                        ->sum('weight');
            $data['last_weight']   = 100 - $totalWeight;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 修改部门指标
         *
         * @param Request $request
         * editQuota
         * author: walker
         * Date: 2019/11/22
         * Time: 15:06
         * Note:
         */
        public function editQuota(Request $request)
        {
            $request->validate([
                                   'department_id' => 'required|string|exists:department,department_id',
                                   'quota_id'      => 'required|string',
                                   'quota_name'    => 'required|string',
                               ]);

            $departmentName         = DB::table('department')->where(['department_id' => $request->department_id])
                                        ->select('department_name')->first()->department_name;
            $model                  = Quota::find($request->quota_id);
            $model->department_id   = $request->department_id;
            $model->department_name = $departmentName;
            $model->quota_name      = $request->quota_name;
            $model->quota_desc      = $request->quota_desc;
            $model->weight          = $request->weight;
            if ( !empty($request->mobile)) $model->mobile = $request->mobile;
            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改部门指标');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 删除指标
         *
         * @param Request $request
         * delQuota
         * author: walker
         * Date: 2019/11/22
         * Time: 15:52
         * Note:
         */
        public function delQuota(Request $request)
        {
            $request->validate([
                                   'quota_id' => 'required|string',
                               ]);
            $model         = Quota::find($request->quota_id);
            $model->is_del = 1;
            $result        = $model->save();
            if (empty($result)) ajaxReturn(4004, Code::$com[4004]);
            SystemController::sysLog($request, '删除部门指标');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取指标列表
         *
         * @param Request $request
         * getQuotaList
         * author: walker
         * Date: 2019/11/22
         * Time: 15:54
         * Note:
         */
        public function getQuotaList(Request $request)
        {
            $page            = (int)$request->page ?: 1;
            $pageNum         = $request->pageNum ?: 10;
            $pageStart       = ($page - 1) * $pageNum;
            $departmentId    = $request->department_id;
            $where           = [];
            $where['is_del'] = 0;
            if ( !empty($departmentId)) $where['department_id'] = $departmentId;
            $list          = DB::table('quota')->where($where)->offset($pageStart)->limit($pageNum)->get();
            $count         = DB::table('quota')->where($where)->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }
    }
