<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Store;

/**
 * Creates if can and returns an instance of class Store - Adapter for Promis
 *
 * You have to connect this factory:
 * <code>
 * 'services' => [
 *      'factories' => [
 *          zaboy\async\Promise\Store::KEY => StoreFactory::class
 *      ]
 * ]
 * </code>
 *
 * If table is not exist - it will be make.
 * Default name for table is TABLE_NAME = 'promises';
 *
 * You can change it in config:
 * <code>
 * 'PromisesStore' => [
 *      'tableName' => 'another_name'
 * ]
 * </code>
 *
 * Filds in table are:
 *
 * id => promise_id_123456789qwerty
 * state => pending || fulfilled || rejected;
 * result => mix;
 * creation_time = 2216125; UTC time when promise has sarted
 * parent_id => promise_id_123456789qwerty2 - promise that gave birth to it
 * on_fulfilled => string, php callable or callback service name;
 * on_rejected => string, php callable or callback service name;
 *
 * @category   async
 * @package    zaboy
 */
class StoreFactory extends FactoryAbstract
{

    // Service name in config
    const KEY = '#Promises Store';
    //
    const TABLE_NAME = 'promises';
    const KEY_TABLE_NAME = '#table-name';

    /**
     *
     * @var string
     */
    protected $tableName;

    /** @var \Zend\Db\Adapter\Adapter $db */
    protected $db;
    protected $promiseTableData = [
        Store::ID => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 128,
                'nullable' => false
            ]
        ],
        Store::STATE => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 128,
                'nullable' => false
            ]
        ],
        Store::RESULT => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 65535,
                'nullable' => true
            ]
        ],
        Store::ON_FULFILLED => [
            'field_type' => 'Blob',
            'field_params' => [
                'length' => 65535,
                'nullable' => true
            ]
        ],
        Store::ON_REJECTED => [
            'field_type' => 'Blob',
            'field_params' => [
                'length' => 65535,
                'nullable' => true
            ]
        ],
        Store::CREATION_TIME => [
            'field_type' => 'Integer',
            'field_params' => [
                'nullable' => false
            ]
        ],
        Store::PARENT_ID => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 128,
                'nullable' => true
            ]
        ],
    ];

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $config = $container->get('config');

        $this->tableName = isset($config[self::KEY][self::KEY_TABLE_NAME]) ?
                $config[self::KEY][self::KEY_TABLE_NAME] :
                (isset($options[self::KEY_TABLE_NAME]) ?
                        $options[self::KEY_TABLE_NAME] :
                        self::TABLE_NAME)
        ;
        $this->db = $container->has('db') ? $container->get('db') : null;
        if (is_null($this->db)) {
            throw new PromiseException(
            'Can\'t create db Adapter'
            );
        }
        if ($container->has(TableManagerMysql::KEY_IN_CONFIG)) {
            $tableManager = $container->get(TableManagerMysql::KEY_IN_CONFIG);
        } else {
            $tableManager = new TableManagerMysql($this->db);
        }

        $hasPromiseStoreTable = $tableManager->hasTable($this->tableName);
        if (!$hasPromiseStoreTable) {
            $tableManager->rewriteTable($this->tableName, $this->promiseTableData);
        }

        return new Store($this->tableName, $this->db);
    }

}
