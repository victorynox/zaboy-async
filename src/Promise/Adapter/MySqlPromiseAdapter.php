<?php

namespace zaboy\async\Promise\Adapter;

use zaboy\rest\DataStore\DbTable;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\TableGateway\TableGateway;
use zaboy\rest\DataStore\Interfaces\ReadInterface;

/**
 *
 *
 * id => promise_id_123456789qwerty
 * state => pending || fulfilled || rejected;
 * result => mix;
 * cancel_fn => string, php callable or callback service name;
 * wait_fn => string, php callable or callback service name;
 * wait_list =>  json array of promise_id;
 * handlers = json array of arrays [string promise_id, string - callable $onFulfilled, string - callable $onRejected];
 * actual_time_end = 2216125; UTC time when promise can be deleted
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
    const MAX_ENDING_TIME = 'max_ending_time';
    //
    const CANCEL_FN = 'cancel_fn';
    const WAIT_FN = 'wait_fn';
    //
    const PARENT_ID = 'parent_id';
    const ON_FULFILLED = 'on_fulfilled';
    const ON_REJECTED = 'on_rejected';

}
