<?php

    namespace App\Models\V1;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\DB;

    class User extends Model
    {
        //指定表名
        protected $table = "users";
        //    public static $table

        /**
         * 更新用户
         *
         * @param array $userId
         * @param array $data
         *
         * @return bool|void
         * update
         * author: walker
         * Date: 2019/11/19
         * Time: 15:36
         * Note:
         */
        public function updateUser($userId, $data)
        {
            if (empty($userId)){
                return false;
            }
            $where       = [];
            $where['id'] = $userId;
            DB::table($this->table)->where($where)->update($data);
        }

        /**
         * 获取用户列表
         *
         * @param        $where
         * @param string $order
         * @param int    $page
         * @param int    $pageNum
         *
         * @return mixed
         * getUserList
         * author: walker
         * Date: 2019/11/20
         * Time: 18:43
         * Note:
         */
        public function getUserList($userName = '', $mobile = '', $order = '', $page = 1, $pageNum = 20)
        {

            $pageStart = ($page - 1) * $pageNum;
            $field     = 'users.name,users.email,users.mobile,users.created_at,users.status,d.department_name,r.role_name,
        users.id,ur.user_id,ur.role_id,r.role_id,users.department_id,d.department_id';
            $list = [];
            $list['list'] = $this->where('name','like', "%$userName%")
                                 ->where('mobile','like',"%$mobile%")
                                 ->selectRaw($field)
                                 ->leftjoin('user_role as ur', 'users.id', '=', 'ur.user_id')
                                 ->leftjoin('role as r', 'ur.role_id', '=', 'r.role_id')
                                 ->leftjoin('department as d', 'users.department_id', '=', 'd.department_id')
                                 ->limit($pageNum)
                                 ->offset($pageStart)
                                 ->get()
                                 ->toArray();
            $list['count'] = $this->where('name','like', "%$userName%")
                                  ->where('mobile','like',"%$mobile%")
                                  ->selectRaw($field)
                                  ->leftjoin('user_role as ur', 'users.id', '=', 'ur.user_id')
                                  ->leftjoin('role as r', 'ur.role_id', '=', 'r.role_id')
                                  ->leftjoin('department as d', 'users.department_id', '=', 'd.department_id')
                                  ->count();
            $list['page']=$page;
            return $list;
        }
    }
