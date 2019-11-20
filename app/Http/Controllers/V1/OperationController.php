<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\Operation;
    use Illuminate\Http\Request;

    class OperationController extends Controller
    {
        //
        /**
         * 添加操作
         * addOperation
         * author: walker
         * Date: 2019/11/20
         * Time: 15:24
         * Note:
         */
        public function addOperation(Request $request)
        {

            $request->validate([
                                   'operation_name' => 'required|string|max:30|unique:operation',
                                   'menu_id'        => 'required|string|max:30',
                                   'route'          => 'required|string|max:30|unique:operation',
                               ]);
            $model                 = new Operation();
            $model->operation_name = $request->operation_name;
            $model->route          = $request->route;
            $result                = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 获取操作列表
         * getOperationList
         * author: walker
         * Date: 2019/11/20
         * Time: 15:34
         * Note:
         */
        public function getOperationList()
        {

        }
    }
