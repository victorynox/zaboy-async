<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Callback;

use zaboy\async\Callback\Interfaces\ServicesInitableInterface;
use zaboy\async\Callback\CallbackException;

/**
 * Abstract class for serializable objects which contain not serializable properties
 *
 * @category   async
 * @package    zaboy
 */
trait ServicesInitableTrait //implements ServicesInitableInterface
{

    /**
     * Init it in constuctor.
     *
     * This property has contane array like:
     * [ propertyName => serviceName, anotherProperty => nextServiseMame...]
     *
     * @var array
     */
    protected $servicesList = [];

    /**
     *
     * Return array [ propertyName => serviceName, anotherProperty => nextServiseMame...]
     */
    public function getServicesList()
    {
        return $this->servicesList;
    }

    /**
     *
     * @param array $services  array [ propertyName => serviceObject, anotherProperty => nextServiseObject...]
     */
    public function setServices($services)
    {
        foreach ($services as $propertyName => $value) {
            if (!property_exists(static::class, $propertyName)) {
                throw new CallbackException("Property $propertyName is not exist");
            }
            if (isset($this->$propertyName) && $this->$propertyName instanceof ServicesInitableInterface) {
                $this->$propertyName->setServices($value);
                continue;
            }
            $this->$propertyName = $value;
        }
    }

    public function __sleep()
    {
        $properties = get_object_vars($this);
        $serializeKeys = [];
        $servicesPropertiesList = array_keys($this->getServicesList());
        foreach ($properties as $propertyName => $value) {
            if (in_array($propertyName, $servicesPropertiesList)) {
                continue;
            }
            if ($value instanceof \Closure) {
                $this->$propertyName = new SerializableClosure($value);
            }
            if ($value instanceof ServicesInitableInterface) {
                $this->servicesList[$propertyName] = $value->getServicesList();
            }
            $serializeKeys[] = $propertyName;
        }

        return $serializeKeys;
    }

    public function __wakeup()
    {

    }

}
