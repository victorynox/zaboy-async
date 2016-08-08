<?php

namespace zaboy\async\Queue\Queue;

use Zend\Db\TableGateway\TableGateway;
use zaboy\rest\DataStore\Interfaces\ReadInterface;

/**
 * Store for Queuees
 *
 *
 * @category   async
 * @package    zaboy
 */
class Store extends TableGateway
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

}
