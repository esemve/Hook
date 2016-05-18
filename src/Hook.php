<?php

namespace Esemve\Hook;

class Hook
{
    protected $watch = [];
    protected $stop = [];
    protected $mock = [];
    protected $testing = false;

    /**
     * Visszaadja egy hook által válaszolt értéket
     * @param string $hook Hook name
     * @param array $params
     * @param callable $callback
     * @return null|void
     */
    public function get($hook, $params = [], callable $callback = null)
    {
        $callbackObject = $this->createCallbackObject($callback, $params);

        $output = $this->returnMockIfDebugModeAndMockExists($hook);
        if ($output) {
            return $output;
        }

        $output = $this->run($hook, $params, $callbackObject);

        if (!$output) {
            $output = $callbackObject->call();
        }

        unset($callbackObject);
        return $output;
    }

    /**
     * Megszakítja az összes további erre a hookra feliratkozott event futását
     * @param string $hook Hook name
     */
    public function stop($hook)
    {
        $this->stop[$hook] = true;
    }

    /**
     * Hookra feliratkozás
     * @param string $hook Hook name
     * @param $priority
     * @param $function
     */
    public function listen($hook, $function, $priority = null)
    {
        $caller = debug_backtrace()[0];

        if (empty($this->watch[$hook])) {
            $this->watch[$hook] = [];
        }

        if (!is_numeric($priority)) {
            $priority = null;
        }

        $this->watch[$hook][$priority] = [
            'function' => $function,
            'caller' => [
                'file' => $caller['file'],
                'line' => $caller['line'],
                'class' => $caller['class']
            ]
        ];

        ksort($this->watch[$hook]);
    }

    /**
     * Visszaadja, hogy milyen hookok vannak regisztrálva
     * @return array
     */
    public function getHooks()
    {
        $hookNames = (array_keys($this->watch));
        ksort($hookNames);
        return $hookNames;
    }

    /**
     * Visszaadja, hogy milyen hookok esetén mi van benne hívásfában
     * @param string $hook
     * @return array
     */
    public function getEvents($hook)
    {
        $output = [];

        foreach ($this->watch[$hook] as $key => $value) {
            $output[$key] = $value['caller'];
        }

        return $output;
    }

    /**
     * Teszteléshez használható. A megadott nevű mock a második paraméterben megadott
     * értéket fogja válaszolni.
     * @param string $name Hook name
     * @param mixed $return Válasz
     */
    public function mock($name, $return)
    {
        $this->testing = true;
        $this->mock[$name] = ['return' => $return];
    }

    /**
     * Visszaadja a hook által beállított értéket abban az esetben, ha teszt üzemmódban
     * vagyunk és van beállított hook
     * @param string $hook Hook név
     * @return null|mixed
     */
    protected function returnMockIfDebugModeAndMockExists($hook)
    {
        if ($this->testing) {
            if (array_key_exists($hook, $this->mock)) {
                $output = $this->mock[$hook]['return'];
                unset($this->mock[$hook]);
                return $output;
            }
        }
        return null;
    }

    /**
     * Return a new callback object
     * @param callable $callback function
     * @param array $params parameters
     * @return \Esemve\Hook\Callback
     */
    protected function createCallbackObject($callback, $params)
    {
        return new Callback($callback, $params);
    }

    /**
     * Run hook events
     * @param string $hook Hook name
     * @param array $params Parameters
     * @param \Esemve\Hook\Callback $callback Callback object
     * @return mixed
     */
    protected function run($hook, $params, Callback $callback)
    {
        $output = null;

        if (array_key_exists($hook, $this->watch)) {
            if (is_array($this->watch[$hook])) {
                foreach ($this->watch[$hook] as $function) {
                    if (!empty($this->stop[$hook])) {
                        unset($this->stop[$hook]);
                        break;
                    }

                    array_unshift($params, $output);
                    array_unshift($params, $callback);

                    $output = call_user_func_array($function['function'], $params);
                }
            }
        }

        return $output;
    }
}
