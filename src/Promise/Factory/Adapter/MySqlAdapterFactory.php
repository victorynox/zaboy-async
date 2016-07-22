<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Factory\Adapter;

use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\DataStoreException;
use zaboy\rest\FactoryAbstract;
use Zend\Db\TableGateway\TableGateway;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\rest\DataStore\DataStoreAbstract as RestDataStore;

/**
 * Creates if can and returns an instance of class Queue\Adapter\DataStoresAbstract - Adapter for Promis
 *
 * Class MySqlAdapterFactory
 *
 * Any comgig needs for this factory, but ypu have to connect this factory:
 *
 * <code>
 * 'services' => [
 *      'factories' => [
 *          'MySqlPromiseAdapter' => 'zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory'
 *      ]
 * ]
 * </code>
 *
 * id => promise_id_123456789qwerty
 * state => pending || fulfilled || rejected;
 * result => mix;
 * cancel_fn => string, php callable or callback service name;
 * wait_fn => string, php callable or callback service name;
 * wait_list =>  json array of promise_id;
 * handlers = json array of arrays [string promise_id, string - callable $onFulfilled, string - callable $onRejected];
 * time_in_flight = 2216125; UTC time when promise has sarted
 * parent_id => promise_id_123456789qwerty2 - promise that gave birth to it
 * on_fulfilled => string, php callable or callback service name;
 * on_rejected => string, php callable or callback service name;
 *
 * @category   async
 * @package    zaboy
 */
class MySqlAdapterFactory extends FactoryAbstract
{

    const KEY_PROMISE_ADAPTER = 'MySqlPromiseAdapter';
    const PROMISE_TABLE_NAME = 'promises';
    const KEY_PROMISE_TABLE_NAME = 'tableName';

    /**
     *
     * @var string
     */
    protected $tableName;

    /** @var \Zend\Db\Adapter\Adapter $db */
    protected $db;

    /** @var  \zaboy\rest\DataStore\DbTable */
    protected $dataStore;
    protected $promiseTableData = [
        MySqlPromiseAdapter::PROMISE_ID => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 128,
                'nullable' => false
            ]
        ],
        MySqlPromiseAdapter::CLASS_NAME => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 256,
                'nullable' => false
            ]
        ],
        MySqlPromiseAdapter::STATE => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 128,
                'nullable' => false
            ]
        ],
        MySqlPromiseAdapter::RESULT => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 65535,
                'nullable' => true
            ]
        ],
        MySqlPromiseAdapter::CANCEL_FN => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 65535,
                'nullable' => true
            ]
        ],
        MySqlPromiseAdapter::ON_FULFILLED => [
            'field_type' => 'Blob',
            'field_params' => [
                'length' => 65535,
                'nullable' => true
            ]
        ],
        MySqlPromiseAdapter::ON_REJECTED => [
            'field_type' => 'Blob',
            'field_params' => [
                'length' => 65535,
                'nullable' => true
            ]
        ],
        MySqlPromiseAdapter::TIME_IN_FLIGHT => [
            'field_type' => 'Integer',
            'field_params' => [
                'nullable' => false
            ]
        ],
        MySqlPromiseAdapter::PARENT_ID => [
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
        $this->tableName = isset($options[self::KEY_PROMISE_TABLE_NAME]) ? $options[self::KEY_PROMISE_TABLE_NAME] : self::PROMISE_TABLE_NAME;
        $this->db = $container->has('db') ? $container->get('db') : null;
        if (is_null($this->db)) {
            throw new DataStoreException(
            'Can\'t create DbTableAdapter for Promise'
            );
        }

        if ($container->has('TableManagerMysql')) {
            $tableManager = $container->get('TableManagerMysql');
        } else {
            $tableManager = new TableManagerMysql($this->db);
        }

        $hasPromiseTableData = $tableManager->hasTable($this->tableName);

        if (!$hasPromiseTableData) {
            $tableManager->rewriteTable($this->tableName, $this->promiseTableData);
        }

        $mySqlPromiseAdapter = new MySqlPromiseAdapter($this->tableName, $this->db);

        return $mySqlPromiseAdapter;
    }

}
