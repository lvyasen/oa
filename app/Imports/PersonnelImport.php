<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PersonnelImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //如果需要去除表头
        unset($collection[0]);
        //$collection是数组形式
        $this->createData($collection);
    }

    public function createData($collection)
    {
        fp($collection);
    }
}
