<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Queue\Broker;

use ReputationVIP\QueueClient\QueueClientInterface;
use ReputationVIP\QueueClient\QueueClient;
use zaboy\scheduler\Callback\CallbackManager;

/**
 * QueueBroker for Queue Client
 *
 * @category   async
 * @package    zaboy
 */
class QueueBroker implements QueueBrokerInterface
{

    const DEFAULT_MSG_IN_QUERY = 100;

    /**
     * <code>
     * [
     *      QueueClient => [
     *          Queue1 => [
     *              'workerName' => 'callbackService Name1'
     *               'messagesNumberInQuery' => 10
     *          ]
     *          Queue2 => [
     *              'workerName' => 'callbackService Name1'
     *          ]
     *      ],
     *      nextQueueClient => [
     *          nextQueue1 => [...
     * ]
     * </code>
     *
     * @var array
     */
    var $queuesParams;

    /**
     * <code>
     * [
     *      'QueueClient' => object,
     *      'nextQueueClient' => object,
     * ]
     * </code>
     *
     * @var array
     */
    var $queuesClientsInstances;

    /**
     *
     * @var CallbackManager
     */
    var $callbackManager;

    public function __construct($callbackManager, $queuesParams, $queuesClientsInstances)
    {
        $this->callbackManager = $callbackManager;
        $this->queuesParams = $queuesParams;
        $this->queuesClientsInstances = $queuesClientsInstances;
        $this->createAllQueues();
    }

    public function runAllWorkers()
    {
        $this->runWorkers();
    }

    public function runHighPriorityWorkers()
    {
        $this->runWorkers('HIGH');
    }

    protected function createAllQueues()
    {
        foreach ($this->queuesClientsInstances as $queueClientName => $queueClientNameInstance) {
            /* @var $queueClientNameInstance \zaboy\async\Queue\Client\Client */
            $existedQueues = $queueClientNameInstance->listQueues();
            foreach ($this->queuesParams[$queueClientName] as $queueNameFromParams => $val) {
                if (!in_array($queueNameFromParams, $existedQueues)) {
                    $queueClientNameInstance->createQueue($queueNameFromParams);
                }
            }
        }
    }

    protected function runWorkers($priority = null)
    {
        $queueClientsNames = $this->getQueueClientsNames();
        foreach ($queueClientsNames as $queueClientName) {
            $queuesNames = $this->getQueuesByClient($queueClientName);
            foreach ($queuesNames as $queueName) {
                $worker = $this->getQueueWorkerInstance($queueClientName, $queueName);
                $queueClient = $this->getQueueClientInstance($queueClientName);

                //$numberOfMessages = $queueClient->getNumberMessages($queueName);
                if (isset($this->queuesParams[$queueClientName][$queueName]['messagesNumberInQuery'])) {
                    $numberOfMessages = $this->queuesParams[$queueClientName][$queueName]['messagesNumberInQuery'];
                } else {
                    $numberOfMessages = self::DEFAULT_MSG_IN_QUERY;
                }

                $messages = $queueClient->getMessages($queueName, $numberOfMessages, $priority);
                foreach ($messages as $message) {
                    /* @var $worker \zaboy\scheduler\Callback\Interfaces\CallbackInterface */
                    $worker->call([$message]);
                    ////Params are sent to call() as array
                    // $message = [
                    //      'id' => '1_ManagedQueue11__576522deb5ad08'
                    //      'Body' => Array (...)
                    //      'priority' => 'HIGH'
                    //      'time_in_flight' => 1466245854
                    // ]
                }
            }
        }
    }

    /**
     *
     * @return QueueClient
     */
    protected function getQueueClientsNames()
    {
        return array_keys($this->queuesParams);
    }

    /**
     *
     * @param string $queueClientName
     * @return QueueClientInterface
     */
    protected function getQueueClientInstance($queueClientName)
    {
        return $this->queuesClientsInstances[$queueClientName];
    }

    protected function getQueuesByClient($queueClientName)
    {
        return array_keys($this->queuesParams[$queueClientName]);
    }

    protected function getQueueWorkerInstance($queueClientName, $queueName)
    {
        $queueClientParams = $this->queuesParams[$queueClientName];
        $workerName = $queueClientParams[$queueName]['workerName'];
        $worker = $this->callbackManager->get($workerName);
        return $worker;
    }

}
