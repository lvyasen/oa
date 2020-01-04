<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Resume;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;

    class TalentController extends Controller
    {
        //
        /**
         * 添加简历
         *
         * @param Request $request
         * addTalent
         * author: walker
         * Date: 2020/1/3
         * Time: 17:53
         * Note:
         */
        public function addTalent(Request $request)
        {
            $request->validate([
                                   'user_name'    => 'required|string|max:30',
                                   'user_company' => 'required|string|max:100',
                                   'current_job'  => 'required|string|max:100',
                                   'user_mobile'  => 'required|string|max:11',
                                   'source'       => 'required|string|max:100',
                               ]);

            $model                 = new Resume();
            $model->user_name      = $request->user_name;
            $model->user_company   = $request->user_company;
            $model->current_job    = $request->current_job;
            $model->user_mobile    = $request->user_mobile;
            $model->source         = strtolower($request->source);
            $model->publisher      = $request->user()->id;
            $model->publisher_name = $request->user()->name;
            $model->status         = 0;
            if ($request->file('annex')){
                $path         = $request->file('annex')->store('public/annex');
                $url          = Storage::url($path);
                $model->annex = $url;
            };
            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加简历成功');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改用户简历
         *
         * @param Request $request
         * editTalent
         * author: walker
         * Date: 2020/1/3
         * Time: 18:14
         * Note:
         */
        public function editTalent(Request $request)
        {
            $request->validate([
                                   'id' => 'required|string|exists:resume',
                               ]);

            $model = Resume::find($request->id);
            if ( !empty($request->user_name)) $model->user_name = $request->user_name;
            if ( !empty($request->user_company)) $model->user_company = $request->user_company;
            if ( !empty($request->current_job)) $model->current_job = $request->current_job;
            if ( !empty($request->user_mobile)) $model->user_mobile = $request->user_mobile;
            if ( !empty($request->source)) $model->source = $request->source;
            if ( !empty($request->status)) $model->status = $request->status;
            if ($request->file('annex')){
                $path         = $request->file('annex')->store('public/annex');
                $url          = Storage::url($path);
                $model->annex = $url;
            };
            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改用户简历信息');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 删除用户简历信息
         *
         * @param Request $request
         * delTalent
         * author: walker
         * Date: 2020/1/3
         * Time: 18:19
         * Note:
         */
        public function delTalent(Request $request)
        {
            $request->validate([
                                   'id' => 'required|string|exists:resume',
                               ]);

            $model            = Resume::find($request->id);
            $model->is_delete = 1;

            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '删除用户简历信息');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取人才信息列表
         *
         * @param Request $request
         * getTalentList
         * author: walker
         * Date: 2020/1/3
         * Time: 18:20
         * Note:
         */
        public function getTalentList(Request $request)
        {
            $page            = (int)$request->page ?: 1;
            $pageNum         = $request->pageNum ?: 10;
            $pageStart       = ($page - 1) * $pageNum;
            $where           = [];
            $where['is_del'] = 0;
            $startTime       = $request->start_time ? strtotime($request->start_time) : 0;
            $endTime         = $request->end_time ? strtotime($request->end) : time();
            $table           = DB::table('resume');
            if ( !empty($request->user_name)) $table->where('user_name', 'like', '%' . $request->user_name . '%');
            if ( !empty($request->user_mobile)) $table->where('user_name', 'like', '%' . $request->user_mobile . '%');
            if ( !empty($request->source)) $table->where('user_name', 'like', '%' . $request->source . '%');
            if ( !empty($request->publisher)) $where['publisher'] = $request->publisher;
            if ( !empty($request->status)) $where['publisher'] = $request->status;
            $where['is_delete'] = 0;
            $table->whereBetween('add_time', [date('Y-m-d H:i:s', $startTime), date('Y-m-d H:i:s', $endTime)]);
            $list          = $table->where($where)->offset($pageStart)->limit($pageNum)->get();
            $count         = $table->where($where)->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 下载简历
         * @param Request $request
         *
         * @return mixed
         * downloadAnnex
         * author: walker
         * Date: 2020/1/4
         * Time: 9:40
         * Note:
         */
        public function downloadAnnex(Request $request)
        {
            $request->validate([
                                   'id' => 'required|string|exists:resume',
                               ]);
            $info = DB::table('resume')
                      ->where(['id' => $request->id])
                      ->first();
            $info = toArr($info);
            if (empty($info['annex'])){
                ajaxReturn(4001, '该用户没有附件');
            }
            switch ($request->type) {
                case'1':
                    break;
                default:
                    return Storage::download($info['annex']);
                    break;
            }
        }
    }
