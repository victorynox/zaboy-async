<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async;

/**
 * EntityAbstract
 *
 * @category   async
 * @package    zaboy
 */
abstract class EntityAbstract extends AsyncAbstract
{

    /**
     * @var array
     */
    public $data;

    /**
     * EntityAbstract constructor.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        parent::__construct();
        $this->data = $data;

        if (!isset($this->data[StoreAbstract::ID])) {
            $this->data[StoreAbstract::ID] = $this->makeId();
        }

        if (!isset($this->data[StoreAbstract::CREATION_TIME])) {
            $this->data[StoreAbstract::CREATION_TIME] = (int) (time() - date('Z'));
        }
    }

    /**
     * Returns the ID of Entity
     *
     * @return mixed
     */
    public function getId()
    {

        if (isset($this->data[StoreAbstract::ID])) {
            return $this->data[StoreAbstract::ID];
        } else {
            $exceptionClass = $this::EXCEPTION_CLASS;
            throw new $exceptionClass(
                "ID is not set."
            );
        }
    }

    /**
     * Returns the raw data of Entity
     *
     * @return array
     */
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
