<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Queue\Broker;

/**
 * QueueBrokerInterhace Interface for Queue Client
 *
 * @category   async
 * @package    zaboy
 */
interface QueueBrokerInterhace
{

    /**
     * Run workers for all queues which are maneged by this broker
     */
    public function runAllWorkers();

    /**
     * Run workers for highpriority queues only  which are maneged by this broker
     */
    public function runHighPriorityWorkers();
}
