<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use App\Models\V1\GaConfig;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Spatie\Analytics\AnalyticsFacade;
    use Spatie\Analytics\Period;

    class GAController extends Controller
    {
        /**
         * 获取活跃用户
         *
         * @param Request $request
         * getActiveUser
         * author: walker
         * Date: 2019/11/26
         * Time: 9:15
         * Note:
         */
        public function getGaApiData(Request $request)
        {
            $request->validate([
                                   'date_type'  => 'required|Integer',
                                   'start_time' => 'nullable|date',
                                   'end_time'   => 'nullable|date',
                                   'view_id'    => 'required|string|exists:ga_config',
                                   'api_type'   => 'required |string ',
                               ]);
            if ($request->start_time || $request->end_time){
                $date = $this->getDate(4, '', $request->start_time, $request->end_time);
            } else {
                $date = $this->getDate($request->date_type, $request->date_val);
            };
            try {
                AnalyticsFacade::setViewId($request->view_id);
                $analyticsData = $this->getGaMethod($request->api_type, $date);
                $data['list']  = $analyticsData;
                ajaxReturn(200, Code::$com[200], $data);
            } catch (\Exception $e) {
                ajaxReturn(4005, $e->getMessage());
            }
        }
        public function getGaUserType(Request $request)
        {
            $request->validate([
                                   'date_type'  => 'required|Integer',
                                   'start_time' => 'nullable|date',
                                   'end_time'   => 'nullable|date',
                                   'view_id'    => 'required|string|exists:ga_config',
                               ]);
            if ($request->start_time || $request->end_time){
                $date = $this->getDate(4, '', $request->start_time, $request->end_time);
            } else {
                $date = $this->getDate($request->date_type, $request->date_val);
            };
            try {
                AnalyticsFacade::setViewId($request->view_id);
//                $sexData = $this->getGaMethod('other')
                $data = [];
                ajaxReturn(200, Code::$com[200], $data);
            } catch (\Exception $e) {
                ajaxReturn(4005, $e->getMessage());
            }
        }

        /**
         * GA日期转换
         *
         * @param        $dateType
         * @param        $dateVal
         * @param string $startTime
         * @param string $endTime
         *
         * @return Period
         * @throws \Exception
         * getDate
         * author: walker
         * Date: 2019/11/26
         * Time: 11:54
         * Note:
         */
        private function getDate($dateType, $dateVal, $startTime = '', $endTime = '')
        {
            $dateVal = (int)$dateVal;
            switch ($dateType) {
                case '1'://day
                    $dateObj = Period::days($dateVal);
                    break;
                case '2'://months
                    $dateObj = Period::months($dateVal);
                    break;
                case '3'://years
                    $dateObj = Period::years($dateVal);
                    break;
                case '4'://自定义日期
                    $startTime = new \DateTime($startTime);
                    $endTime   = new \DateTime($endTime);
                    $dateObj   = Period::create($startTime, $endTime);
                    break;
                default:
                    ajaxReturn(4001, Code::$com[4001]);
                    break;

            }
            return $dateObj;
        }

        /**
         * 获取GA查询接口数据
         *
         * @param        $apiType
         * @param        $date
         * @param string $maxRes
         * @param string $metrics
         * @param array  $others
         *
         * @return mixed
         * getGaMethod
         * author: walker
         * Date: 2019/11/26
         * Time: 13:49
         * Note:
         */
        private function getGaMethod($apiType, $date, $maxRes = '', $metrics = '', $others = [])
        {
            switch ($apiType) {
                case 'fetchVisitorsAndPageViews'://访客和综合浏览量
                    $analyticsData = AnalyticsFacade::fetchVisitorsAndPageViews($date);
                    break;
                case 'fetchTotalVisitorsAndPageViews'://访客总数和综合浏览量
                    $analyticsData = AnalyticsFacade::fetchTotalVisitorsAndPageViews($date);
                    break;
                case 'fetchMostVisitedPages'://访问最多的页面
                    $analyticsData = AnalyticsFacade::fetchMostVisitedPages($date, $maxRes);
                    break;
                case 'fetchTopReferrers'://热门引荐来源
                    $analyticsData = AnalyticsFacade::fetchTopReferrers($date, $maxRes);
                    break;
                case 'fetchUserTypes'://用户类型
                    $analyticsData = AnalyticsFacade::fetchUserTypes($date);
                    break;
                case 'fetchTopBrowsers'://热门浏览器
                    $analyticsData = AnalyticsFacade::fetchTopBrowsers($date, $maxRes);
                    break;

                default:
                    ajaxReturn(4006, Code::$com[4006]);
                    break;
            }
            return toArr($analyticsData);

        }

        /**
         * 获取ga网站配置
         *
         * @param Request $request
         * getGaWebSitList
         * author: walker
         * Date: 2019/11/26
         * Time: 11:25
         * Note:
         */
        public function getGaWebSitList(Request $request)
        {

            $page          = (int)$request->page ?: 1;
            $pageNum       = $request->pageNum ?: 10;
            $pageStart     = ($page - 1) * $pageNum;
            $table         = DB::table('ga_config');
            $list          = $table->offset($pageStart)->limit($pageNum)->get();
            $count         = $table->count();
            $data          = [];
            $data['list']  = $list;
            $data['page']  = $page;
            $data['count'] = $count;
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * 添加Ga配置
         *
         * @param Request $request
         * addGaConfig
         * author: walker
         * Date: 2019/11/26
         * Time: 11:32
         * Note:
         */
        public function addGaConfig(Request $request)
        {
            $request->validate([
                                   'website_name' => 'required|string|max:30|unique:ga_config',
                                   'view_id'      => 'required|string|max:30|unique:ga_config',
                               ]);

            $model                 = new GaConfig();
            $model->website_name   = $request->website_name;
            $model->view_id        = $request->view_id;
            $model->website_domain = $request->website_domain;
            $result                = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '添加GA配置');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 修改GA信息
         *
         * @param Request $request
         * editGaconfig
         * author: walker
         * Date: 2019/11/26
         * Time: 11:36
         * Note:
         */
        public function editGaconfig(Request $request)
        {
            $request->validate([
                                   'website_id'   => 'required|string:exists:ga_config',
                                   'website_name' => 'required|string',
                                   'view_id'      => 'required|string',
                               ]);

            $model                 = GaConfig::find($request->website_id);
            $model->website_name   = $request->website_name;
            $model->view_id        = $request->view_id;
            $model->website_domain = $request->website_domain;
            $result                = $model->save();
            if (empty($result)) ajaxReturn(4002, Code::$com[4002]);
            SystemController::sysLog($request, '修改GA配置信息');
            ajaxReturn(200, Code::$com[200]);
        }

        /**
         * 删除GA配置
         *
         * @param Request $request
         * delGaConfig
         * author: walker
         * Date: 2019/11/26
         * Time: 11:45
         * Note:
         */
        public function delGaConfig(Request $request)
        {
            $request->validate([
                                   'website_id' => 'required|string|max:30|exists:ga_config',
                               ]);
            $where               = [];
            $where['website_id'] = $request->website_id;
            $result              = DB::table('ga_config')->delete($request->website_id);
            if (empty($result)) ajaxReturn(4004, Code::$com[4004]);
            SystemController::sysLog($request, '删除GA配置');
            ajaxReturn(200, Code::$com[200]);
        }
    }
