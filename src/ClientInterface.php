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
