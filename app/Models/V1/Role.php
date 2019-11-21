<?php

    namespace App\Models\V1;

    use Illuminate\Database\Eloquent\Model;

    class Role extends Model
    {
        //
        protected $table      = 'role';
        protected $primaryKey = 'role_id';
        protected $dateFormat = 'U';
        const CREATED_AT = 'add_time';
        const UPDATED_AT = 'update_time';
    }
