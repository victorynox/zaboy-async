<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Message\Message;

use zaboy\async\Message\Interfaces\MessageInterface;
use zaboy\async\Message\MessageException;
use zaboy\async\Message\Store;
use zaboy\async\Message\Message\FulfilledMessage;
use zaboy\async\Message\Message\RejectedMessage;
use zaboy\async\EntityAbstract;

/**
 * Message
 *
 * @category   async
 * @package    zaboy
 */
class MessageInFlight extends Message
{

    /**
     *
     * @param Store $data
     */
    public function pullMessage()
    {
        throw new MessageException('Message is alredy pulled');
    }

}
