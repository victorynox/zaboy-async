<?php

namespace zaboy\test\async\Callback\Example;

class CallableService
{

    public function __invoke($value = null)
    {
        return 'CallableService resolve ' . $value;
    }

}
