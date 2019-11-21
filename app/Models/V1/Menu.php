<?php

    namespace App\Models\V1;

    use Illuminate\Database\Eloquent\Model;

    class Menu extends Model
    {
        //
        protected $table      = 'menu';
        protected $primaryKey = 'menu_id';
        protected $dateFormat = 'U';
        const CREATED_AT = 'add_time';
        const UPDATED_AT = 'update_time';

        /**
         * 修改菜单状态
         * @param $menuId
         *
         * @return bool
         * editStatus
         * author: walker
         * Date: 2019/11/21
         * Time: 16:44
         * Note:
         */
        public function editStatus($menuId)
        {
            $where                  = [];
            $where['menu_id'] = $menuId;
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
