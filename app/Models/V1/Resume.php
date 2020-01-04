<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    //
    protected $table      = 'resume';
    protected $primaryKey = 'id';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';
}
