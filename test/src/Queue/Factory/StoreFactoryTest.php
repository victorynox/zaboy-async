<?php

namespace zaboy\test\async\Queue\Factory;

use zaboy\test\async\StoreFactoryAbstract;
use zaboy\rest\TableGateway\TableManagerMysql;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class StoreFactoryTest extends StoreFactoryAbstract
{

    const FACTORY_CLASS = 'zaboy\async\Queue\Factory\StoreFactory';
    const STORE_CLASS = 'zaboy\async\Queue\Store';

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $adapter = $this->container->get('db');
        $tableManagerMysql = new TableManagerMysql($adapter);
        $tableManagerMysql->deleteTable('messages_test');
        $tableManagerMysql->deleteTable('promises_test');
    }

    public function testStoreFactory__invoke__TableNameFromConfig()
    {
        global $testCase;
        $testCase = 'table_for_test';
        $factoryClass = static::FACTORY_CLASS;
        $storeClass = static::STORE_CLASS;

        $this->container = include './config/container.php';
        $this->object = new $factoryClass();
        $this->tableManagerMysql = $this->container->get(TableManagerMysql::KEY_IN_CONFIG);

// if tables is absent
        $this->tableName = $factoryClass::TABLE_NAME . '_test';
        $this->tableManagerMysql->deleteTable($this->tableName);
        $this->assertFalse(
                $this->tableManagerMysql->hasTable($this->tableName)
        );
        /* @var $store Store */
        $store = $this->container->get($factoryClass::KEY);
        $this->assertInstanceOf(
                $storeClass, $store
        );
        $this->assertInstanceOf(
                'zaboy\async\Promise\Store', $store->getPromisesStore()
        );
        $this->assertInstanceOf(
                'zaboy\async\Message\Store', $store->getMessagesStore()
        );

// if tables is present
        $this->assertTrue(
                $this->tableManagerMysql->hasTable($this->tableName)
        );
        $store = $this->container->get($factoryClass::KEY);
        $this->assertInstanceOf(
                $storeClass, $store
        );
        $this->tableManagerMysql->deleteTable($this->tableName);
        $testCase = 'default';
    }

}
