<?php

namespace zaboy\async\Promise\Adapter;

use Zend\Db\TableGateway\TableGateway;
use zaboy\rest\DataStore\Interfaces\ReadInterface;

/**
 *
 *
 * id => promise_id_123456789qwerty
 * state => pending || fulfilled || rejected;
 * result => mix;
 * creation_time = 2216125; UTC time when promise has sarted
 * parent_id => promise_id_123456789qwerty2 - promise that gave birth to it
 * on_fulfilled => string, php callable or callback service name;
 * on_rejected => string, php callable or callback service name;
 *
 * @category   async
 * @package    zaboy
 */
class MySqlPromiseAdapter extends TableGateway
{

    //PROMISE_ADAPTER_DATA_STORE
    //
    //'id' - unique id of promise: promise_id_123456789qwerty
    const PROMISE_ID = ReadInterface::DEF_ID;
    const STATE = 'state';
    const RESULT = 'result';
    const CREATION_TIME = 'creation_time';
    //
    const PARENT_ID = 'parent_id';
    const ON_FULFILLED = 'on_fulfilled';
    const ON_REJECTED = 'on_rejected';

    /**
     *
     * @return int Grivich UTC time in seconds
     */
    public function getUtcTime()
    {
        return (int) (time() - date('Z'));
    }

    /**
     *
     * @return int Grivich UTC time in microseconds
     */
    public function getUtcMicrotime()
    {
        return round(microtime(1) - date('Z'), 6);
    }

}
