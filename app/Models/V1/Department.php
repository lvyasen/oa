<?php

    namespace App\Models\V1;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\DB;

    class Department extends Model
    {
        protected $table      = 'department';
        protected $primaryKey = 'department_id';
        protected $dateFormat = 'U';
        const CREATED_AT = 'add_time';
        const UPDATED_AT = 'updated_time';

        /**
         * 获取部门列表
         *
         * @param int $page
         * @param int $pageNum
         *
         * @return mixed
         * getDepartmentList
         * author: walker
         * Date: 2019/11/20
         * Time: 9:44
         * Note:
         */
        public function getDepartmentList(int $page = 1, int $pageNum = 20)
        {
            if (empty($page)){
                $page = 1;
            }
            $pageStart       = ($page - 1) * $pageNum;
            $where           = [];
            $where['status'] = 1;
            return $this
                ->where($where)
                ->orderBy('updated_time', 'desc')
                ->limit($pageNum)
                ->offset($pageStart)
                ->get()
                ->toArray();
        }

        /**
         * 修改部门状态
         *
         * @param $departmentId
         *
         * @return bool
         * editStatus
         * author: walker
         * Date: 2019/11/20
         * Time: 10:00
         * Note:
         */
        public function editStatus($departmentId)
        {
            $where                  = [];
            $where['department_id'] = $departmentId;
            $status                 = $this
                ->where($where)
                ->select('status')
                ->first()
                ->toArray();
            if ( !isset($status)) return false;
            $status = !$status['status'];
            return $this->where($where)->update(['status' => $status]);
        }

        public function editDepartment($departmentId, $data)
        {
            if (empty($departmentId)) return false;
            $where                  = [];
            $where['department_id'] = $departmentId;
            return $this->where($where)->update($data);
        }

    }
