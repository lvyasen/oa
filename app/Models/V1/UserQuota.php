<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class UserQuota extends Model
{
    //
    protected $table      = 'user_quota';
    protected $primaryKey = 'user_quota_id';
    protected $dateFormat = 'U';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';
}
