<?php

    namespace App\Models\Erp;

    use Illuminate\Database\Eloquent\Model;

    class SiteWeb extends Base
    {
        //
        protected $table      = 'siteweb';
        protected $primaryKey = 'web_id';
        protected $dateFormat = 'U';
        const CREATED_AT = 'add_time';
        const UPDATED_AT = 'update_time';
    }
