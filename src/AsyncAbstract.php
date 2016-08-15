<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async;

/**
 * Client
 *
 * @category   async
 * @package    zaboy
 */
abstract class AsyncAbstract
{

    const EXCEPTION_CLASS = '\Exception';

    const ID_SEPARATOR = '_';

    private $idPattern;

    /**
     * AsyncAbstract constructor.
     */
    public function __construct()
    {
        $this->idPattern = '/(' . $this->getPrefix() . '__[0-9]{10}_[0-9]{6}__[a-zA-Z0-9_]{23})/';
    }


    /**
     * Creates ID for the entity.
     *
     * An algorithm of creation ID is common for the all entities except for prefix string.
     *
     * For example for Promise it will be 'promise_', for Task - 'task_' etc.
     *
     * @return string
     */
    protected function makeId()
    {
        $time = sprintf('%0.6f', (microtime(1) - date('Z')));
        $idWithDot = uniqid(
                $this->getPrefix() . self::ID_SEPARATOR . self::ID_SEPARATOR
                . $time . self::ID_SEPARATOR . self::ID_SEPARATOR
                , true
        );
        $id = str_replace('.', self::ID_SEPARATOR, $idWithDot);

        return $id;
    }

    /**
     * Checks string for the match ID.
     *
     * @param string $param
     * @return bool
     */
    public function isId($param)
    {
        $array = [];
        $regExp = $this->idPattern;
        if (is_string($param) && preg_match_all($regExp, $param, $array)) {
            return $array[0][0] == $param;
        } else {
            return false;
        }
    }

    /**
     * Returns the Prefix for Id
     *
     * @return string
     */
    public function getPrefix()
    {
        return strtolower(explode('\\', get_class($this))[2]);
    }

    /**
     *
     *
     * @param $stringOrException
     * @param array $idArray
     * @return array
     */
    public function extractId($stringOrException, $idArray = [])
    {
        if (is_null($stringOrException)) {
            return $idArray;
        }
        if ($stringOrException instanceof \Exception) {
            $array = $this->extractId($stringOrException->getPrevious(), $idArray);
            $idArray = $this->extractId($stringOrException->getMessage(), $array);
            return $idArray;
        }
        $array = [];
        if (preg_match_all($this->idPattern, $stringOrException, $array)) {
            return array_merge(array_reverse($array[0]), $idArray);
        } else {
            return [];
        }
    }

}
