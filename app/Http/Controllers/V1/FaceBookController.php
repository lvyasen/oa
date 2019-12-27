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
        private static $access_token  = 'EAAHSAZC8QX2ABAIWmuZAqqmvk5kBZCCKJ42XE9nOLOclun2YbREnWp15qZB5nsRsrkxkIVcfLPn6ePJZANonuuUeUcghciFM0CnUrmcXoxbEUPIqlxkfFVNrcMiX49NLXVFtRRmHnZAVROwyP7VF3O25QTb136vV6lP4k7meWV8iBehOxZBrzMWRQcdb1NrSNsZD';
        private static $ad_account_id = '512389582708576';
        private static $app_secret    = '6d0ad231608af84f6c214a64bceb24dc';
        private static $app_id        = '568673980363082';//沙箱账号

        //
        public function getAds(Request $request)
        {

            $fields = [
                'spend',

                'clicks'
                //                'unique_actions:link_click',
            ];
            $params = [
                'level'      => 'account',
                'filtering'  => [],
                'breakdowns' => [],
                'time_range' => ['since' => '2019-01-10', 'until' => '2019-12-10'],
            ];
            $api    = Api::init(self::$app_id, self::$app_secret, self::$access_token);
            $api->setLogger(new CurlLogger());
            $adAccount = new AdAccount(self::$ad_account_id);
            //            $res = $adAccount->getUsers();
            $res = $adAccount->getInsights($fields, $params)->getResponse()->getContent();
            fp($res);

        }
    }
