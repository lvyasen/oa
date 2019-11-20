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
    public function updateUser($userId,$data)
    {
        if(empty($userId)){
            return false;
        }
        $where = [];
        $where['id'] = $userId;
        DB::table($this->table)->where($where)->update($data);
    }

    /**
     * 获取用户列表
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
    public function getUserList($where,$order='',$page=1,$pageNum=20)
    {

        $pageStart = ($page-1)*$pageNum;
        return $this->where($where)->limit($pageNum)->offset($pageStart)->get()->toArray();
    }
}
