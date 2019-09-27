<?php

namespace Esemve\Hook\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void listen($hook, $function, $priority = null)
 * @method static mixed get($hook, $params = [], callable $callback = null, $htmlContent = '')
 * @method static void stop($hook)
 * @method static void mock($name, $return)
 * @method static array getListeners()
 * @method static array getEvents($hook)
 * @method static array getHooks()
 */
class Hook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Hook';
    }
}
