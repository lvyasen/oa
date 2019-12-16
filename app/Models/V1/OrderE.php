<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class OrderE extends Model
{
    //
    protected $table      = 'e_orders';
    protected $primaryKey = 'id';
    protected $dateFormat = 'U';
    public $timestamps = false;
}
