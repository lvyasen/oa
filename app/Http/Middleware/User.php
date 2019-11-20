<?php

    namespace App\Http\Middleware;

    use App\Dictionary\Code;
    use Closure;
    use Dingo\Api\Facade\Route;

    /**
     * 校验用户权限中间件
     * Class User
     * User: walker
     * Date: 2019/11/20
     * Time: 15:17
     * Caret:
     *
     * @package App\Http\Middleware
     */
    class User
    {
        /**
         * Handle an incoming request.
         *
         * @param \Illuminate\Http\Request $request
         * @param \Closure                 $next
         *
         * @return mixed
         */
        public function handle($request, Closure $next)
        {

//            $userInfo = $request->user()->toArray();
//            if (empty($userInfo)) ajaxReturn(4002,Code::$user['not_login']);
//            $userId = $userInfo['id'];
//            $route = $request->route()->getActionMethod();
//            print_r($route);
//            die();
                    return $next($request);
        }
    }
