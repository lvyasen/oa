<?php

    namespace App\Models\V1;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\Request;

    /**
     * 权限数据库操作
     * Class Permission
     * User: walker
     * Date: 2019/11/20
     * Time: 15:22
     * Caret:
     *
     * @package App\Models\V1
     */
    class Permission extends Model
    {

        protected $table      = 'permission';
        protected $primaryKey = 'permission_id';
        protected $dateFormat = 'U';
        const CREATED_AT = 'add_time';
        const UPDATED_AT  = 'update_time';

        /**
         * 修改状态
         * @param $permissionId
         *
         * @return bool
         * editStatus
         * author: walker
         * Date: 2019/11/21
         * Time: 17:06
         * Note:
         */
        public function editStatus($permissionId)
        {
            $where                  = [];
            $where['permission_id'] = $permissionId;
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
