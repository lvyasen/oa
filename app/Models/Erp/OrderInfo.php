<?php

namespace App\Models\Erp;

use Illuminate\Database\Eloquent\Model;

class OrderInfo extends Base
{
    //
    protected $table      = 'order_info';
    protected $primaryKey = 'order_id';
    protected $dateFormat = 'U';
    public $timestamps = false;
}
