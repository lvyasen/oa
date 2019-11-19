<?php

    namespace App\Http\Controllers\V1;

    use App\Dictionary\Code;
    use App\Http\Controllers\Controller;
    use Dotenv\Validator;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;
    use App\User;
    use Webmozart\Assert\Assert;

    class AuthController extends Controller
    {
        //
        /**
         * 用户注册
         *
         * @param  [string] name
         * @param  [string] email
         * @param  [string] password
         * @param  [string] password_confirmation
         *
         * @return [string] message
         */
        public function signUp(Request $request)
        {
            $request->validate([
                                   'mobile'        => 'required|string',
                                   'email'         => 'required|string|email',
                                   'password'      => 'required|string',
                                   'age'           => 'string',
                                   'name'          => 'required|string',
                                   'sex'           => 'string',
                                   'department_id' => 'required|string',
                               ]);

            $user = new User([
                                 'name'          => $request->name,
                                 'email'         => $request->email,
                                 'age'           => $request->age,
                                 'mobile'        => $request->mobile,
                                 'sex'           => $request->sex,
                                 'department_id' => $request->department_id,
                                 'password'      => bcrypt($request->password),
                                 'login_ip'      => $request->getClientIp(),
                             ]);

            $user->save();
            ajaxReturn(200, Code::$com[200]);

        }

        /**
         * 用户登录获取access_token
         *
         * @param  [string] email
         * @param  [string] password
         * @param  [boolean] remember_me
         *
         * @return [string] access_token
         * @return [string] token_type
         * @return [string] expires_at
         */
        public function logIn(Request $request)
        {


            $request->validate([
                                   'mobile'      => 'required|string',
                                   'password'    => 'required|string',
                                   'remember_me' => 'boolean',
                               ]);
            $credentials = request(['mobile', 'password']);
            if ( !Auth::attempt($credentials)) ajaxReturn(4002, Code::$user['login_fail']);

            $user        = $request->user();
            $tokenResult = $user->createToken('Personal Access Token');
            $token       = $tokenResult->token;
            if ($request->remember_me)
                $token->expires_at = Carbon::now()->addWeeks(1);

            $token->save();

            $data                 = [];
            $data['access_token'] = $tokenResult->accessToken;
            $data['token_type']   = 'Bearer';
            $data['expires_at']   = Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString();
            ajaxReturn(200, Code::$com[200], $data);
        }

        /**
         * Logout user (Revoke the token)
         *
         * @return [string] message
         */
        public function logout(Request $request)
        {
            $request->user()->token()->revoke();

            return response()->json([
                                        'message' => 'Successfully logged out',
                                    ]);
        }

        /**
         * 获取用户信息
         * Get the authenticated User
         *
         * @return [json] user object
         */
        public function getUserInfo(Request $request)
        {
            $userInfo = $request->user();
            if ($userInfo){
                //更新用户
                $userModel        = new \App\Models\V1\User();
                $data['login_ip'] = $request->getClientIp();
                $userModel->updateUser($userInfo['id'], $data);
                $userInfo['login_ip'] = $request->getClientIp();
                ajaxReturn(200, Code::$user['get_user_info_success'], $userInfo);
            }
            ajaxReturn(4001, Code::$user['get_user_info_fail']);
        }
    }
