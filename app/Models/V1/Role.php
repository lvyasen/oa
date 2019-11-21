<?php

    namespace App\Models\V1;

    use Illuminate\Database\Eloquent\Model;

    class Role extends Model
    {
        //
        protected $table      = 'role';
        protected $primaryKey = 'role_id';
        protected $dateFormat = 'U';
        const CREATED_AT = 'add_time';
        const UPDATED_AT = 'update_time';

        /**
         * 修改状态
         * @param $roleId
         *
         * @return bool
         * editStatus
         * author: walker
         * Date: 2019/11/21
         * Time: 16:53
         * Note:
         */
        public function editStatus($roleId)
        {
            $where                  = [];
            $where['role_id'] = $roleId;
            $status                 = $this
                ->where($where)
                ->select('status')
                ->first()
                ->toArray();
            if ( !isset($status)) return false;
            $status = !$status['status'];
            return $this->where($where)->update(['status' => $status]);
        }
    }
