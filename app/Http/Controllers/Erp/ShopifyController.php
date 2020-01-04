<?php

    namespace App\Http\Controllers\Erp;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Http\Controllers\V1\SystemController;
    use App\Models\Erp\ShopifyApi;
    use App\Models\Erp\SiteWeb;
    use App\Models\V1\ShopifyAuth;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\URL;
    use PHPShopify\ShopifySDK;

    class ShopifyController extends Controller
    {
        private static $appId     = '2a78a3fa78042ba0c4031551e962b330';
        private static $appSecret = '7437cbe99d1832c56fc04b017397858a';
        //接口权限列表
        private static $scope = "read_orders,write_products,read_customers,read_product_listings,read_analytics,read_checkouts,read_reports,write_reports,read_resource_feedbacks,read_shopify_payments_payouts,read_fulfillments";

        /**
         * 下载订单队列
         *
         * @param Request $request
         * countProducts
         * author: walker
         * Date: 2019/12/5
         * Time: 14:32
         * Note:
         */
        public function getPrePullData(Request $request)
        {

            $request->validate([
                                   'down_time' => 'nullable|date',
                               ]);
            $downTime = $request->down_time ? strtotime($request->down_time) : time();
            $shopify = new  ShopifyApi();
            $count = $shopify->countData($downTime);

        }

        /**
         * 拉取shopify订单数据
         *
         * @param Request $request
         * pullProductsData
         * author: walker
         * Date: 2019/12/6
         * Time: 15:52
         * Note:
         */
        public function pullProductsData(Request $request)
        {

            $request->validate([
                                   'start_time' => 'nullable|date',
                                   'end_time'   => 'nullable|date',
                               ]);

            $shopify = new  ShopifyApi();

            $count = $shopify->pullOrderData();
        }

        /**
         * 拉取shopify订单数据
         *
         * @param Request $request
         * pullProductsData
         * author: walker
         * Date: 2019/12/6
         * Time: 15:52
         * Note:
         */
        public function getCustomersData(Request $request)
        {

            $request->validate([
                                   'down_time' => 'nullable|date',
                                   'web_id'    => 'required|string',
                               ]);
            $downTime = $request->down_time ? strtotime($request->down_time) : time();
            $webId    = $request->web_id;
            $shopify  = new  ShopifyApi();
            $count    = $shopify->countData($webId, $downTime, 1);


        }

        public function getReport(Request $request)
        {
            $config                   = [
                'ShopUrl'     => '712styles.myshopify.com',
                'AccessToken' => 'b8137111b70a01d69e1b907db2b5d1ce',
            ];
            $shopify                  = new ShopifySDK($config);
            $params                   = [];
            $params['updated_at_min'] = '2019-12-07';
            //            $params['limit']               = 100;
            //            $products             = $shopify->Order->get();
            $totalOrder = $shopify->Order->count($params);
            $shop       = '712styles';
            $token      = 'b8137111b70a01d69e1b907db2b5d1ce';
            //            $totalOrder = shopifyCall($token, $shop, "/admin/api/2019-10/orders/count.json",$params, 'GET');
            //            $products = shopifyCall($token, $shop, "/admin/api/2019-10/reports.json",$params, 'POST');
            fp($totalOrder);

        }


        /**
         * shopify授权
         *
         * @param Request $request
         * shopifyInstall
         * author: walker
         * Date: 2019/12/7
         * Time: 13:40
         * Note:
         */
        public function shopifyInstall(Request $request)
        {

            $request->validate([
                                   'shop' => 'required|string|exists:shopify_auth',
                               ]);
            //            $shop         = '712styles';
            $shop         = $request->shop;
            $api_key      = self::$appId;
            $scopes       = self::$scope;
            $redirect_uri = $_SERVER['REQUEST_SCHEME'] . "://{$_SERVER['HTTP_HOST']}/api/shopifyGenerateToken";

            // Build install/approval URL to redirect to
            $install_url = "https://" . $shop . ".myshopify.com/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

            // Redirect
            header("Location: " . $install_url);
            die();
        }

        /**
         * shopify授权回调
         *
         * @param Request $request
         * shopifyCallback
         * author: walker
         * Date: 2019/12/6
         * Time: 18:51
         * Note:
         */
        public function shopifyGenerateToken(Request $request)
        {
            //        [code] => 7a99e9c8823ececacfe2c287af2b25be
            //        [hmac] => b5079a91bed523304868060e936ff0ed46f0c69037187213fc87fa3062eb113a
            //        [shop] => 712styles.myshopify.com
            //        [timestamp] => 1575685325
            // Set variables for our request
            $api_key       = self::$appId;//API key
            $shared_secret = self::$appSecret;//API secret key
            $params        = $_GET; // Retrieve all request parameters
            $hmac          = $_GET['hmac']; // Retrieve HMAC request parameter

            $params = array_diff_key($params, ['hmac' => '']); // Remove hmac from params
            ksort($params); // Sort params lexographically
            $computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

            // Use hmac data to check that the response is from Shopify or not
            if (hash_equals($hmac, $computed_hmac)){

                // Set variables for our request
                $query = [
                    "client_id"     => $api_key, // Your API key
                    "client_secret" => $shared_secret, // Your app credentials (secret key)
                    "code"          => $params['code'] // Grab the access key from the URL
                ];

                // Generate access token URL
                $access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

                // Configure curl client and execute request
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $access_token_url);
                curl_setopt($ch, CURLOPT_POST, count($query));
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
                $result = curl_exec($ch);
                curl_close($ch);

                // Store the access token
                $result       = json_decode($result, true);
                $access_token = $result['access_token'];
                $shop         = explode('.', $_GET['shop'])[0];
                $info         = DB::table('shopify_auth')
                                  ->where(['shop' => $shop])
                                  ->first('shopify_id');
                if ( !empty($info)){
                    DB::table('shopify_auth')
                      ->where(['shop' => $shop])
                      ->update([
                                   'access_token' => $access_token,
                                   'update_time'  => time(),
                                   'status'       => 1,
                               ]);
                } else {
                    DB::table('shopify_auth')
                      ->where(['shop' => $shop])
                      ->insert([
                                   'shop'         => $shop,
                                   'access_token' => $access_token,
                                   'add_time'     => time(),
                               ]);
                }
                ajaxReturn(200, '成功');
                // Show the access token (don't do this in production!)

            } else {
                // Someone is trying to be shady!
                die('This request is NOT from Shopify!');
            }

        }


    }
