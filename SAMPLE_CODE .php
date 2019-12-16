<?php
    /**
     * Copyright (c) 2015-present, Facebook, Inc. All rights reserved.
     *
     * You are hereby granted a non-exclusive, worldwide, royalty-free license to
     * use, copy, modify, and distribute this software in source code or binary
     * form for use in connection with the web services and APIs provided by
     * Facebook.
     *
     * As with any software that integrates with the Facebook platform, your use
     * of this software is subject to the Facebook Developer Principles and
     * Policies [http://developers.facebook.com/policy/]. This copyright notice
     * shall be included in all copies or substantial portions of the software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
     * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
     * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
     * DEALINGS IN THE SOFTWARE.
     *
     */

    require __DIR__ . '/vendor/autoload.php';

    use FacebookAds\Object\AdAccount;
    use FacebookAds\Object\AdsInsights;
    use FacebookAds\Api;
    use FacebookAds\Logger\CurlLogger;

    $access_token = 'EAAHSAZC8QX2ABAEv2vLhXTERa7nEqZBEccSdqwVvVmhs8cNAjBYhx8HyaZA9E58EgU3CTgM6Ez9yfFEgngRUvFgOIVZCPkIHKjod7FK2bwH7Am5MMmKs7knZCLxsCTrOBhEKuObi5AlMxxFiSRkGBqOEyqlYGsqFRQYOAkBrD5SLaZCIHeoq8QjO08afspGyAZD';
    $ad_account_id = 'act_568673980363082';
    $app_secret = '6d0ad231608af84f6c214a64bceb24dc';
    $app_id = '512389582708576';

    $api = Api::init($app_id, $app_secret, $access_token);
    $api->setLogger(new CurlLogger());

    $fields = array(
        'spend',
        'actions:link_click',
        'unique_actions:link_click',
    );
    $params = array(
        'level' => 'account',
        'filtering' => array(),
        'breakdowns' => array(),
        'time_range' => array('since' => '2019-11-10','until' => '2019-12-10'),
    );
    echo json_encode((new AdAccount($ad_account_id))->getInsights(
        $fields,
        $params
    )->getResponse()->getContent(), JSON_PRETTY_PRINT);

