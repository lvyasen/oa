<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    protected $table      = 'ship';
    protected $primaryKey = 'id';
//    protected $dateFormat = 'U';
    const CREATED_AT = 'addTime';
    const UPDATED_AT = 'updateTime';
}
