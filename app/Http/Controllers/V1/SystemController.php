<?php

    namespace App\Http\Controllers\V1;

    use App\Http\Controllers\Controller;
    use App\Models\V1\System;
    use Illuminate\Http\Request;

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
        public static function sysLog($request, $note = '')
        {
            $model              = new System();
            $model->action_name = $request->route()->getActionName();
            $model->route       = $request->route()->getActionMethod();
            $model->note        = $note;
            $model->user_id     = $request->user()->id;
            $model->params      = json_encode($request->input(), true);
            $model->ip          = $request->getClientIp();
            $model->add_time    = time();
            $model->save();
        }

    }
