<?php

    namespace App\Models\V1;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\DB;

    class Common extends Model
    {
        //
        /**
         * 公用修改状态
         *
         * @param $table
         * @param $primaryKey
         * @param $primaryVal
         *
         * @return bool|int
         * editStatus
         * author: walker
         * Date: 2019/11/22
         * Time: 9:33
         * Note:
         */
        public static function editStatus($table, $primaryKey, $primaryVal, $field = 'status')
        {
            if (empty($table) || empty($primaryKey) || empty($primaryVal)) return false;
            $where              = [];
            $where[$primaryKey] = $primaryVal;
            $status             = DB::table($table)
                                    ->where($where)
                                    ->select($field)
                                    ->first()
                                    ;

            if ( !isset($status)) return false;
            $status = !$status->$field;
            return DB::table($table)->where($where)->update(['status' => $status]);
        }
    }
