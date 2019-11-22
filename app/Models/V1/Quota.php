<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Quota extends Model
{
    //
    protected $table      = 'quota';
    protected $primaryKey = 'quota_id';
    protected $dateFormat = 'U';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';
}
