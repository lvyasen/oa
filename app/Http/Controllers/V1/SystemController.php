<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Exports\SystemExport;
    use App\Exports\UsersExport;
    use App\Http\Controllers\Controller;
    use App\Models\V1\System;
    use Illuminate\Http\Request;

    use Maatwebsite\Excel\Facades\Excel;
    use function foo\func;

    class SystemController extends Controller
    {
        //
        /**
         * 系统日志
         *
         * @param        $request
         * @param string $note
         * sysLog
         * author: walker
         * Date: 2019/11/20
         * Time: 17:31
         * Note:
         */
        public static function sysLog($request = '', $note = '')
        {
            $model = new System();
            $model->action_name = $request->route()->getActionName();
            $model->route       = $request->route()->getActionMethod();
            $model->note        = $note;
            $model->user_id     = !empty($request->user())?$request->user()->id:0;
            $model->params      = json_encode($request->input(), true);
            $model->ip          = $request->getClientIp();
            $model->user_name   = !empty($request->user())?$request->user()->name:'system';
            $model->add_time    = time();
            $model->save();

        }

        /**
         * 获取系统日志列表
         *
         * @param Request $request
         * getSystemLog
         * author: walker
         * Date: 2019/11/21
         * Time: 18:35
         * Note:
         */
        public function getSystemLog(Request $request)
        {
            $page      = $request->page ?: 1;
            $pageNum   = $request->pageNum ?: 10;
            $pageStart = ($page - 1) * $pageNum;
            $where     = [];
            $startTime = strtotime($request->start_time) ?: strtotime('-7 days');
            $endTime   = $request->end_time ? strtotime($request->end_time) : time();
            if ($request->user_name) $where['user_name'] = $request->user();
            $logList       = System::where($where)
                                   ->whereBetween('add_time', [$startTime, $endTime])
                                   ->offset($pageStart)
                                   ->limit($pageNum)
                                   ->orderBy('add_time', 'DESC')
                                   ->get()
                                   ->toArray();
            $count         = System::where($where)
                                   ->whereBetween('add_time', [$startTime, $endTime])
                                   ->count();
            $data          = [];
            $data['list']  = $logList;
            $data['count'] = $count;
            $data['page']  = $page;
            //            return   Excel::download(new UsersExport($logList),'test.xlsx');

            ajaxReturn(200, Code::$com[200], $data);
        }

    }

