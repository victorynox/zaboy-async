<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Queue\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Queue\QueueException;
use zaboy\async\Queue\Store;
use zaboy\async\Message;

/**
 * Creates if can and returns an instance of class Store for Queue
 *
 * You have to connect this factory:
 * <code>
 * 'services' => [
 *      'factories' => [
 *          zaboy\async\QueueStore::KEY => StoreFactory::class
 *      ]
 * ]
 * </code>
 *
 * If table is not exist - it will be make.
 * Default name for table is TABLE_NAME = 'queues';
 *
 * You can change it in config:
 * <code>
 * StoreFactory::KEY => [
 *      StoreFactory::KEY_TABLE_NAME => 'another_name'
 * ]
 * </code>
 *
 * Filds in table are:
 *
 * id => promise_id_123456789qwerty
 *
 *
 * @category   async
 * @package    zaboy
 */
class StoreFactory extends FactoryAbstract
{

    // Service name in config
    const KEY = '#Queue Store';
    //
    const TABLE_NAME = 'queues';
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
        Store::NAME => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 128,
                'nullable' => true
            ]
        ],
        Store::CREATION_TIME => [
            'field_type' => 'Integer',
            'field_params' => [
                'nullable' => false
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
            throw new QueueException(
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

        $messagesStore = $container->has(Message\Factory\StoreFactory::KEY) ?
                $container->get(Message\Factory\StoreFactory::KEY) : null;
        if (is_null($messagesStore)) {
            throw new QueueException(
            'Can\'t create $messages Store'
            );
        }
        return new Store($this->tableName, $this->db, $messagesStore);
    }

}
