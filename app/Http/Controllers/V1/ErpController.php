<?php

    namespace App\Http\Controllers\V1;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Phpro\SoapClient\Exception\SoapException;
    use Phpro\SoapClient\Soap\HttpBinding\SoapRequest;

    class ErpController extends Controller
    {
        //
        protected static $ebDomain  = 'http://tuliang-eb.eccang.com';
        protected static $wmsDomain = 'http://tuliang.eccang.com';
        protected static $userName  = 'admin';
        protected static $userPwd   = 'QE4opraf7';

        public function test()
        {
            $service = 'getWarehouse';
            $params  = [];
            $re      = $this->soapRequest($service, 'wms', $params);
            print_r($re);
        }



        /**
         * Erp SOAP请求封装
         *
         * @param       $service
         * @param       $systemCode
         * @param array $params
         *
         * @return array|mixed
         * @throws \SoapFault
         * soapRequest
         * author: walker
         * Date: 2019/11/23
         * Time: 10:49
         * Note:
         */
        private function soapRequest($service, $systemCode, $params = [])
        {
            $method     = 'callService';
            $data       = [
                'ask'     => 'Failure',
                'message' => 'SoapRequest Error',
                'data'    => [],
            ];
            $systemCode = strtoupper($systemCode);//code转成大写
            /**
             * 校验编码
             */
            if ( !in_array($systemCode, ['EB', 'WMS'])){
                $data['message'] = 'system code not find';
                return $data;
            };
            //设置域名
            $domian = $systemCode == 'EB' ? self::$ebDomain : self::$wmsDomain;
            if (empty($domian)){
                $data['message'] = 'domain not empty';
                return $data;
            }
            $domian = trim($domian, '/');//去除斜线
            $wsdl   = $domian . '/default/svc-open/web-service-v2';

            $requestData = [
                'userName' => self::$userName,
                'userName' => self::$userName,
                'userPass' => self::$userPwd,
                'service'  => $service,
            ];
            if ( !empty($params)) $requestData['paramsJson'] = json_encode($params);
            try {
                $option   = [
                    'trace' => true,//调试信息
                ];
                $client   = new \SoapClient($wsdl, $option);
                $res      = $client->__soapCall($method, ['parameters' => $requestData]);
                $response = $res->response;
                $data     = json_decode($response, true);

            } catch (SoapException $e) {
                $data['message'] = $e->getMessage();
            }
            return $data;
        }
    }
