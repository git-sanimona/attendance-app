<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
// use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable; たぶんいらないから後で消す
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        // Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);　たぶんいらないので後で消す

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        /** 今回はたぶん2要素認証(ワンタイムパスワードなど)は使わないためあとで消す
         *RateLimiter::for('two-factor', function (Request $request) {
         *    return Limit::perMinute(5)->by($request->session()->get('login.id'));
         *});
         */

        // 管理者用ログインフォームのビュー指定
        Fortify::loginView(function (Request $request) {

            // URLが /admin/login で名前付きルートをadmin.loginでルーティングで設定しておく
            if ($request->is('admin/login') || $request->routeIs('admin.login')) {
                return view('admin.admin-login');
            }

            // それ以外（一般ユーザー用の）画面指定
            return view('user.user-login');
        });

        // ユーザー登録フォームのビュー指定
        Fortify::registerView(function () {
            return view('user.register');
        });
    }
}
