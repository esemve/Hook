<?php

namespace Esemve\Hook;

class Callback
{
    protected $function;
    protected $parameters = [];
    protected $run = true;
    protected $output = null;

    public function __construct($function, $parameters = [])
    {
        $this->setCallback($function, $parameters);
    }

    public function setCallback($function, $parameters)
    {
        $this->function = $function;
        $this->parameters = $parameters;
    }

    public function call($parameters = null)
    {
        if ($this->run) {
            $this->run = false;
            return call_user_func_array($this->function, ($parameters ? $parameters : $this->parameters));
        }

        return null;
    }

    public function reset()
    {
        $this->run = true;
    }
}
