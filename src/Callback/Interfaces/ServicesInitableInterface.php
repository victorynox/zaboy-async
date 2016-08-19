<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Callback\Interfaces;

/**
 * Full Interface for Initable Objects
 *
 * Usialy used for init object after unserialize
 *
 * @category   async
 * @package    zaboy
 */
interface ServicesInitableInterface
{

    /**
     *
     * Return array [ propertyName => serviceName, anotherProperty => nextServiseMame...]
     */
    public function getServicesList();

    /**
     *
     * @param array $services  array [ propertyName => serviceObject, anotherProperty => nextServiseObject...]
     */
    public function setServices($services);
}
