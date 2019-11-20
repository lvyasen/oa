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
    
}
