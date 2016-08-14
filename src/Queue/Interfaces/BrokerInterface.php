<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Queue\Interfaces;

/**
 * Full Interface for Queue Broker
 *
 * @category   async
 * @package    zaboy
 */
interface BrokerInterface
{

    public function make($queueName, $priority = null);

    public function get($queueName);

    public function delete($queueName);
}
