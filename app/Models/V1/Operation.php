<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    //
    protected $table      = 'operation';
    protected $primaryKey = 'operation_id';
    protected $dateFormat = 'U';
    const CREATED_AT = 'add_time';
}
