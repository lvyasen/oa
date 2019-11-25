<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    //
    protected $table      = 'material';
    protected $primaryKey = 'material_cost_id';
    protected $dateFormat = 'U';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';
}
