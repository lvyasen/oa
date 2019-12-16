<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ShopifyAuth extends Model
{
    //
    protected $table      = 'shopify_auth';
    protected $primaryKey = 'shopify_id';
    protected $dateFormat = 'U';
    const CREATED_AT = 'add_time';
    const UPDATED_AT = 'update_time';
}
