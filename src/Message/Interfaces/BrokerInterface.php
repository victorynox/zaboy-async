<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Message\Interfaces;

/**
 * Full Interface for Message Broker
 *
 * @category   async
 * @package    zaboy
 */
interface BrokerInterface
{

    public function make($priority = null);

    public function get($messageId);

    public function delete($messageId);
}
