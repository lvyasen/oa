<?php

    namespace App\Models\V1;

    use Illuminate\Database\Eloquent\Model;

    class Menu extends Model
    {
        //
        protected $table      = 'menu';
        protected $primaryKey = 'menu_id';
        protected $dateFormat = 'U';
        const CREATED_AT = 'add_time';
        const UPDATED_AT = 'update_time';
    }
