<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async;

use zaboy\async\StoreAbstract;

/**
 * EntityAbstract
 *
 * @category   async
 * @package    zaboy
 */
abstract class EntityAbstract extends AsyncAbstract
{

    /**
     *
     * @var array
     */
    public $data;

    /**
     *
     * @param Store $store
     */
    public function __construct($data = [])
    {
        $this->data = $data;

        if (!isset($this->data[StoreAbstract::ID])) {
            $this->data[StoreAbstract::ID] = $this->makeId();
        }

        if (!isset($this->data[StoreAbstract::CREATION_TIME])) {
            $this->data[StoreAbstract::CREATION_TIME] = (int) (time() - date('Z'));
        }
    }

    public function getId()
    {

        if (isset($this->data[StoreAbstract::ID])) {
            return $this->data[StoreAbstract::ID];
        } else {
            $exceptionClass = $this::EXCEPTION_CLASS;
            throw new $exceptionClass(
            "id is not set."
            );
        }
    }

    public function getData()
    {
        if (isset($this->data)) {
            return $this->data;
        } else {
            $exceptionClass = $this::EXCEPTION_CLASS;
            throw new $exceptionClass(
            "Data is not set."
            );
        }
    }

}
