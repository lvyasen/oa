<?php

    namespace App\Http\Controllers\Erp;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\Erp\ShopifyApi;
    use App\Models\Erp\SiteWeb;
    use Illuminate\Http\Request;
    use PHPShopify\ShopifySDK;

    class ShopifyApiController extends Controller
    {
        public function getOrderList(Request $request)
        {
            $request->validate([
//                                   'name' => 'required|string|',
                               ]);
            $shopify = new ShopifyApi();
        }
    }
