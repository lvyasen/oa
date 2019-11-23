<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    //
    protected $table      = 'website';
    protected $primaryKey = 'website_id';
    protected $dateFormat = 'U';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';

}
