<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Queue\Interfaces;

/**
 * Full Interface for Queue Client
 *
 * @category   async
 * @package    zaboy
 */
interface ClientInterface
{

    /**
     * @param string $priority
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * @param string $priority
     *
     * @return int
     */
    public function getNumberMessages($priority = null);

    /**
     * @param string $targetQueueName
     *
     */
    public function rename($name);

    /**
     * @param string $priority
     *
     */
    public function purge($priority = null);

    /**
     * @param string $priority
     *
     */
    public function pullMessage($priority = null);

    /**
     *
     * @param type $body
     * @param type $priority
     */
    public function addMessage($body, $priority = null);

    /**
     *
     * @param string $messageId
     */
    public function deleteMessage($messageId);
}
