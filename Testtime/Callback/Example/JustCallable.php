<?php

namespace zaboy\test\async\Callback\Example;

use zaboy\async\Callback\CallbackException;

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

    public function callReturnPromise($value, $promise)
    {
        $promise->resolve('JustCallable::callReturnPromise resolve ' . $value);
        return $promise;
    }

    public function callReturnPromiseAfterSleep($value, $promise)
    {
        sleep(2);
        $promise->resolve('JustCallable::callAfterSleep ' . $value);
        return $promise;
    }

    public function throwException($value)
    {
        throw new \Exception(
        'Exception'
        );
    }

    public function ReturnPromiseException($value, $promise)
    {
        $promise->reject(new CallbackException(
                'ReturnPromiseException'
        ));
        return $promise;
    }

}
