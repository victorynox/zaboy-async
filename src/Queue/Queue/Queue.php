<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Queue\Queue;

use zaboy\async\Queue\Interfaces\QueueInterface;
use zaboy\async\Queue\QueueException;
use zaboy\async\Queue\Store;
use zaboy\async\Queue\Queue\FulfilledQueue;
use zaboy\async\Queue\Queue\RejectedQueue;
use zaboy\async\EntityAbstract;

/**
 * Queue
 *
 * @category   async
 * @package    zaboy
 */
class Queue extends EntityAbstract
{

    /**
     *
     * @param Store $store
     * @throws QueueException
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->data[Store::NAME] = isset($this->data[Store::NAME]) ? $this->data[Store::NAME] : $this->makeName();
    }

    public function setName($name)
    {
        if (!is_string($name)) {
            throw new QueueException('$name must be string');
        }
        $this->data[Store::NAME] = (string) $name;
        return $this->getData();
    }

    protected function makeName()
    {
        return md5($this->getId());
    }

}
