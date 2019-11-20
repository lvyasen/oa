<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    //
    protected $table      = 'system_log';
    protected $primaryKey = 'id';
    protected $dateFormat = 'U';
    public $timestamps = false;
}
