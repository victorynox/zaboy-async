<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Message\Interfaces;

/**
 * Full Interface for Message Client
 *
 * @category   async
 * @package    zaboy
 */
interface ClientInterface
{

    //public function setInFly($inFly); //in_fly
    public function getBody();
}
