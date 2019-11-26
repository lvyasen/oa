<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class GaConfig extends Model
{
    //
    protected $table      = 'ga_config';
    protected $primaryKey = 'website_id';
    protected $dateFormat = 'U';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';
}
