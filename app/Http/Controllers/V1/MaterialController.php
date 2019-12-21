<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Exports\MaterialExport;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Common;
    use App\Models\V1\Material;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;
    use Maatwebsite\Excel\Facades\Excel;


    /**
     * 物料管理
     * Class MaterialController
     * User: walker
     * Date: 2019/11/25
     * Time: 12:02
     * Caret:
     *
     * @package App\Http\Controllers\V1
     */
    class MaterialController extends Controller
    {
        /**
         * 添加物料
         * @param Request $request
         * addMaterial
         * author: walker
         * Date: 2019/12/16
         * Time: 16:33
         * Note:
         */
        public function addMaterial(Request $request)
        {
            $request->validate([
                                   'package_num'          => 'required|integer',
                                   'package_price'        => 'required|string',
                                   'zipper_package_num'   => 'required|integer',
                                   'zipper_package_price' => 'required|string',
                                   'ticket_price'         => 'required|string',
                                   'ticket_num'           => 'required|integer',
                                   'buy_time'             => 'required|date',
                               ]);
            $model                             = new Material();
            $model->package_num                = $request->package_num;
            $model->package_price              = $request->package_price;
            $model->package_total_price        = (int)$request->package_num * number_format($request->package_price, 2);
            $model->zipper_package_num         = $request->zipper_package_num;
            $model->zipper_package_price       = $request->zipper_package_price;
            $model->zipper_package_total_price = number_format($request->zipper_package_price, 2) * (int)$request->zipper_package_num;
            $model->ticket_price               = $request->ticket_price;
            $model->ticket_num                 = $request->ticket_num;
            $model->ticker_total_price         = (int)$request->ticket_num * number_format($request->ticket_price, 2);
            $model->total_price                = $model->package_total_price + $model->zipper_package_total_price + $model->ticker_total_price;
            $model->buy_time                   = strtotime($request->buy_time);
            $model->buy_at                   = date('Y-m-d H:i:s',strtotime($request->buy_time));
            if ($request->file('image')){
                $path            = $request->file('image')->store("public");
                $model->img_path = Storage::url($path);
            };
            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加物料');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改物料
         *
         * @param Request $request
         * editMaterial
         * author: walker
         * Date: 2019/11/25
         * Time: 15:22
         * Note:
         */
        public function editMaterial(Request $request)
        {

            $request->validate([
                                   'material_cost_id'     => 'required|integer|exists:material',
                                   'package_num'          => 'required|integer',
                                   'package_price'        => 'required|string',
                                   'zipper_package_num'   => 'required|integer',
                                   'zipper_package_price' => 'required|string',
                                   'ticket_price'         => 'required|string',
                                   'ticket_num'           => 'required|integer',
                                   'buy_time'             => 'required|date',
                               ]);

            $model                             = Material::find($request->material_cost_id);
            $model->package_num                = $request->package_num;
            $model->package_price              = $request->package_price;
            $model->package_total_price        = (int)$request->package_num * number_format($request->package_price, 2);
            $model->zipper_package_num         = $request->zipper_package_num;
            $model->zipper_package_price       = $request->zipper_package_price;
            $model->zipper_package_total_price = number_format($request->zipper_package_price, 2) * (int)$request->zipper_package_num;
            $model->ticket_price               = $request->ticket_price;
            $model->ticket_num                 = $request->ticket_num;
            $model->ticker_total_price         = (int)$request->ticket_num * number_format($request->ticket_price, 2);
            $model->total_price                = $model->package_total_price + $model->zipper_package_total_price + $model->ticker_total_price;
            $model->buy_time                   = strtotime($request->buy_time);
            $model->buy_at                   = date('Y-m-d H:i:s',strtotime($request->buy_time));
            if ($request->file('image')){
                $path            = $request->file('image')->store("public");
                $model->img_path = Storage::url($path);
            };
            if ( !empty($request->mobile)) $model->mobile = $request->mobile;
            $result = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改物料信息');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 删除物料
         *
         * @param Request $request
         * delMaterial
         * author: walker
         * Date: 2019/11/25
         * Time: 15:25
         * Note:
         */
        public function delMaterial(Request $request)
        {
            $request->validate([
                                   'material_cost_id' => 'required|integer|exists:material',
                               ]);

            $model         = Material::find($request->material_cost_id);
            $model->is_del = 1;
            if ( !empty($request->mobile)) $model->mobile = $request->mobile;
            $result = $model->save();
            if (empty($result)) ajaxReturn(4004, Code::$com[4004]);
            SystemController::sysLog($request, '删除物料信息');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取物料列表
         *
         * @param Request $request
         * getMaterialList
         * author: walker
         * Date: 2019/11/25
         * Time: 15:34
         * Note:
         */
        public function getMaterialList(Request $request)
        {

            $request->validate([
                                   'start_time' => 'nullable|date',
                                   'end_time' => 'nullable|date',
                               ]);
            $page            = (int)$request->page ?: 1;
            $pageNum         = $request->pageNum ?: 10;
            $pageStart       = ($page - 1) * $pageNum;
            $where           = [];
            $where['is_del'] = 0;
            if ( !empty($request->status)) $where['status'] = $request->status;
            $startTime = $request->start_time ? strtotime($request->start_time) : 0;
            $endTime   = $request->end_time ? strtotime($request->end_time) : time();
            $table     = DB::table('material');
            $table->whereBetween('buy_time', [$startTime, $endTime]);
            $list          = $table->where($where)->offset($pageStart)->limit($pageNum)->get();
            $count         = $table->where($where)->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            if (!empty($request->download)){
                return Excel::download(new MaterialExport(toArr($list)), 'test.xlsx');
            };
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 修改物料状态
         *
         * @param Request $request
         * editMateriaStatus
         * author: walker
         * Date: 2019/11/25
         * Time: 15:43
         * Note:
         */
        public function editMateriaStatus(Request $request)
        {
            $request->validate([
                                   'material_cost_id' => 'required|string',
                                   'status'           => 'required|integer',
                               ]);
            $table = DB::table('material');
            $ids   = json_decode($request->material_cost_id, true);

            $res = $table->whereIn('material_cost_id', $ids)->update(['status' => $request->status]);
            if (empty($res)) ajaxReturn(4003, Code::$com[4003]);
            SystemController::sysLog($request, '物料审核');
            ajaxReturn(200, Code::$com[200]);
        }
    }
