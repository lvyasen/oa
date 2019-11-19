<?php

    namespace App\Providers;

    use Dingo\Api\Routing\Route;
    use Illuminate\Http\Request;
    use Dingo\Api\Auth\Provider\Authorization;
    class PassportDingoProvider extends Authorization
    {
        /**
         * Register services.
         *
         * @return void
         */
        public function register()
        {
            //
        }

        public function authenticate(Request $request, Route $route)
        {
            return $request->user();
        }

        public function getAuthorizationMethod()
        {
            return 'bearer';
        }

        /**
         * Bootstrap services.
         *
         * @return void
         */
        public function boot()
        {
            //
        }
    }
