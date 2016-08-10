<?php

namespace zaboy\async;

/**
 * Interface ClientInterface
 *
 * An interface for clients of async entities like Promise, Task etc.
 *
 * @package zaboy\async
 */
interface ClientInterface
{

    /**
     * Creates ID for the entity.
     *
     * An algorithm of creation is common for the all entities except for prefix.
     *
     * For example for Promise it will be 'promise_', for Task - 'task_' etc.
     *
     * @return string
     */
    public function makeId();

    /**
     * Checks string for tha match ID.
     *
     * @return boolean
     */
    public static function isId($param);

    /**
     * Returns an array created from data of entity.
     *
     * @return array mixed
     */
    public function getData();

    /**
     * Returns the Entity ID
     *
     * @return string
     */
    public function getId();
}
