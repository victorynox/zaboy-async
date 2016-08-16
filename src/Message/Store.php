<?php

namespace zaboy\async\Message;

use zaboy\async\StoreAbstract;

/**
 * Store for states of  Message
 *
 * @category   async
 * @package    zaboy
 */
class Store extends StoreAbstract
{

    const QUEUE_ID = 'queue_id';
    const MESSAGE_BODY = 'message_body';
    const PRIORITY = 'priority';
    const PROMISE = 'promise';
    const TIME_IN_FLIGHT = 'time_in_flight';

}
