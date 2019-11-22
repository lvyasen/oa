<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Quota;
    use Illuminate\Http\Request;

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
                                   'department_id'   => 'required|string',
                                   'department_name' => 'required|string',
                                   'quota_name'      => 'required|string',

                               ]);

            $model                  = new Quota();
            $model->department_id   = $request->department_id;
            $model->department_name = $request->department_name;
            $model->quota_name      = $request->quota_name;
            $model->quota_desc      = $request->quota_desc;
            $result                 = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加部门指标');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改部门指标
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
                                   'department_id'   => 'required|string',
                                   'quota_id'        => 'required|string',
                                   'department_name' => 'required|string',
                                   'quota_name'      => 'required|string',
                               ]);

            $model                  = Quota::find($request->quota_id);
            $model->department_id   = $request->department_id;
            $model->department_name = $request->department_name;
            $model->quota_name      = $request->quota_name;
            $model->quota_desc      = $request->quota_desc;
            if ( !empty($request->mobile)) $model->mobile = $request->mobile;
            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改部门指标');
            ajaxReturn(200, Code::$com[200]);
        }
    }
