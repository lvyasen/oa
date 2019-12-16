<?php

    namespace App\Http\Controllers\V1;

    use App\Http\Controllers\Controller;
    use FacebookAds\Api;
    use FacebookAds\Logger\CurlLogger;
    use FacebookAds\Object\AdAccount;
    use FacebookAds\Object\Fields\AdAccountFields;
    use Illuminate\Http\Request;

    class FaceBookController extends Controller
    {
        private static $access_token  = 'EAALQ4mpJm4UBAFNhnXeOzJQkxjSeiXN02HYKyQlMD5VSQuKriHIor7sfIhBiIgJOZCkZB7UsSZBveuYkAmIZAZBjh8U8z0qZCiAaXSRvYTMxbYuJuvowOwt5mYRaFdLOvYE49dDbr3ZCauDjSOxhMxSgYUUvUOVXEGdfTMAl87mbtenihmkPCpgnxMBHIvhdgsZD';
        private static $ad_account_id = 'act_954424211597267';
        private static $app_secret    = 'c600725b9c87e95d7e92e27117a1ddfb';
        private static $app_id        = '102541504485683';

        //
        public function getAds(Request $request)
        {

            $fields = array(
                'spend',
                'account_currency',
                'ad_id',
                'ad_name',
                'account_id',
                'unique_ctr',
                'unique_inline_link_clicks',

            );
            $params = array(
                'level' => 'account',
                'filtering' => array(),
                'breakdowns' => array(),
                'time_range' => array('since' => '2019-11-10','until' => '2019-12-10'),
            );
            $api    = Api::init(self::$app_id, self::$app_secret, self::$access_token);
            $api->setLogger(new CurlLogger());
            $adAccount = new AdAccount(self::$ad_account_id);
//            $res = $adAccount->getUsers();
            $res       = $adAccount->getInsights($fields, $params)->getResponse()->getContent();
            fp($res);

        }
    }