<?php

namespace Esemve\Hook;

use Illuminate\Support\ServiceProvider;
use Blade;

class HookServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootDirectives();
    }

    public function register()
    {
        $this->commands([
            \Esemve\Hook\Console\HookListeners::class,
        ]);

        $this->app->singleton('Hook', function () {
            return new Hook;
        });
    }

    protected function bootDirectives()
    {
        Blade::directive('hook', function ($parameter) {

            $parameter = trim($parameter, '()');
            $parameters = explode(',', $parameter);

            $name = trim($parameters[0], "'");

            return ' <' . '?php

                $__definedVars = (get_defined_vars()["__data"]);
                if (empty($__definedVars))
                {
                    $__definedVars = [];
                }
                $output = \Hook::get("template.'.$name.'",["data"=>$__definedVars],function($data) { return null; });
                if ($output)
                echo $output;
            ?' . '>';
        });
    }
}
