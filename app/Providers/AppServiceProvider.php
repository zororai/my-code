<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // @superadmin / @endsuperadmin — wraps UI elements only Super Admin should see
        Blade::if('superadmin', function () {
            return auth()->check() && auth()->user()->is_super_admin;
        });
    }
}
