<?php

namespace zaboy\test\async\Callback\Example;

class JustCallable
{

    public function __invoke($value)
    {
        return 'JustCallable resolve ' . $value;
    }

    public function call($value)
    {
        return 'JustCallable::call resolve ' . $value;
    }

    public function throwException($value)
    {
        throw new \Exception(
        'Exception'
        );
    }

}
