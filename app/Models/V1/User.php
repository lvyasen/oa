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
        public function getUserList($where,$userName = '', $mobile = '', $order = '', $page = 1, $pageNum = 20)
        {

            $pageStart     = ($page - 1) * $pageNum;
            $field         = 'users.name,users.email,users.mobile,users.created_at,users.status,d.department_name,r.role_name,
        users.id,ur.user_id,ur.role_id,r.role_id,users.department_id,d.department_id';
            $list          = [];
            $list['list']  = $this->where('name', 'like', "%$userName%")
                                  ->where('mobile', 'like', "%$mobile%")
                                  ->selectRaw($field)
                                  ->leftjoin('user_role as ur', 'users.id', '=', 'ur.user_id')
                                  ->leftjoin('role as r', 'ur.role_id', '=', 'r.role_id')
                                  ->leftjoin('department as d', 'users.department_id', '=', 'd.department_id')
                                  ->limit($pageNum)
                                  ->offset($pageStart)
                                  ->get()
                                  ->toArray();
            $list['count'] = $this->where('name', 'like', "%$userName%")
                                  ->where('mobile', 'like', "%$mobile%")
                                  ->selectRaw($field)
                                  ->leftjoin('user_role as ur', 'users.id', '=', 'ur.user_id')
                                  ->leftjoin('role as r', 'ur.role_id', '=', 'r.role_id')
                                  ->leftjoin('department as d', 'users.department_id', '=', 'd.department_id')
                                  ->count();
            $list['page']  = $page;
            return $list;
        }

        /**
         * 获取菜单列表
         *
         * @param $userId
         *
         * @return array|bool
         * getUserMenu
         * author: walker
         * Date: 2019/11/22
         * Time: 11:58
         * Note:
         */
        public static function getUserMenu($userId)
        {
            if ( !$userId) return false;
            $where            = [];
            $where['user_id'] = $userId;
            $roleList         = DB::table('user_role as  ur')
                                  ->leftJoin('role as r', 'ur.role_id', '=', 'r.role_id')
                                  ->get();
            $roleList         = toArr($roleList);
            if (empty($roleList)) return false;
            $menuList = [];
            foreach ($roleList as $key => $val) {
                $menu     = json_decode($val['menu_list'], true);
                $menuList = array_merge($menuList, $menu);
            }
            $menuList            = array_unique($menuList);
            $menuWhere           = [];
            $menuWhere['status'] = 1;
            $allMenuList         = DB::table('menu')->where($menuWhere)->get();
            $allMenuList         = toArr($allMenuList);
            foreach ($allMenuList as $key1 => $val1) {
                if ( !in_array($val1['menu_id'], $menuList)) unset($allMenuList[$key1]);
            }
            $userMenuList = getMenuTree($allMenuList);
            return $userMenuList;
        }

        /**
         * 添加用户角色
         *
         * @param $userId
         * @param $roleList
         *
         * @return bool
         * addUserRole
         * author: walker
         * Date: 2019/11/22
         * Time: 13:31
         * Note:
         */
        public static function addUserRole($userId, array $roleList)
        {
            if ( !$userId || !$roleList) return false;
            //删除用户之前角色
            DB::table('user_role')->where(['user_id' => $userId])->delete();
            foreach ($roleList as $key => $val) {
                $data             = [];
                $data['user_id']  = $userId;
                $data['role_id']  = $val;
                $data['add_time'] = time();
                DB::table('user_role')->insert($data);
            }
            return true;
        }

        /**
         * 获取用户信息
         *
         * @param        $userId
         * @param string $field
         *
         * @return bool
         * getUserInfo
         * author: walker
         * Date: 2019/11/22
         * Time: 17:07
         * Note:
         */
        public static function getUserInfo($userId, $field = 'name')
        {
            if (empty($userId)) return false;
            $where       = [];
            $where['id'] = $userId;
            return DB::table('users')->where($where)->selectRaw($field)->first();
        }
    }
