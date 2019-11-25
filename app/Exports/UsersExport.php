<?php

namespace App\Exports;

use App\User;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($data)
    {
        $this->data = $data;
    }
    //数组转集合
    public function collection()
    {
        return new Collection($this->createData());
    }
    //业务代码
    public function createData()
    {
        fp($this->data);
        $excelData = [];

        //todo 业务
        return [
            ['编号', '姓名', '年龄'],
            [1, '小明', '18岁'],
            [4, '小红', '17岁']
       ];
    }

}
