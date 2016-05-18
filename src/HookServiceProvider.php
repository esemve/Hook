<?php

namespace Esemve\Hook;

use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->singleton('Hook', function () {
            return new Hook;
        });
    }
}
