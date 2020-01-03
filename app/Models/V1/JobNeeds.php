<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class JobNeeds extends Model
{
    //
    protected $table      = 'job_needs';
    protected $primaryKey = 'id';
//    protected $dateFormat = 'U';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';
}
