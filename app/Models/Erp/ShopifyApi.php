<?php

    namespace App\Models\Erp;


    class ShopifyApi
    {
        private $webAccess;
        private $startTime;
        private $endTime;

        public function __construct($webAccess)
        {
            if(empty($webAccess)){
                return false;
            }
            $this->webAccess = $webAccess;
        }

        /**
         * 设置时间
         *
         * @param $startTime
         * @param $endTime
         * setTime
         * author: walker
         * Date: 2019/11/27
         * Time: 16:28
         * Note:
         */
        public function setTime($startTime, $endTime)
        {
            $this->startTime = strtotime($startTime);
            $this->endTime   = strtotime($endTime);
        }

        /**
         * 获取时间字符串
         *
         * @return bool|string
         * getTimeString
         * author: walker
         * Date: 2019/11/27
         * Time: 16:32
         * Note:
         */
        private function getTimeString()
        {
            if (empty($this->startTime) || empty($this->endTime)) return false;
            return "?created_at_min=$this->startTime&created_at_max=$this->endTime";
        }

        private function getApiUrl($apiUrl)
        {
            return $this->webAccess . $apiUrl . $this->getTimeString();
        }

        public function productsCount()
        {
            $this->getApiUrl('.myshopify.com/admin/products/count.json');
        }
    }
