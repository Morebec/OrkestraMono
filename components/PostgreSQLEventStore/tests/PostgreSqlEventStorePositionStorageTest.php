<?php

namespace Tests\Morebec\Orkestra\PostgreSqlEventStore;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStorePositionStorage;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStorePositionStorageConfiguration;
use PHPUnit\Framework\TestCase;

class PostgreSqlEventStorePositionStorageTest extends TestCase
{
    /**
     * @var PostgreSqlEventStorePositionStorage
     */
    private $store;

    /**
     * @var ClockInterface
     */
    private $clock;

    protected function setUp(): void
    {
        $config = new PostgreSqlEventStorePositionStorageConfiguration();

        $connection = DriverManager::getConnection([
            'url' => 'pgsql://postgres@localhost:5432/postgres?charset=UTF8',
        ], new Configuration());
        $this->store = new PostgreSqlEventStorePositionStorage($connection, $config);
        $this->store->clear();
    }

    public function testSet(): void
    {
        $this->assertNull($this->store->get('test'));
        $this->store->set('test', 0);
        $this->assertEquals(0, $this->store->get('test'));
    }

    public function testReset(): void
    {
        $this->store->set('test', 150);
        $this->assertEquals(150, $this->store->get('test'));
        $this->store->reset('test');
        $this->assertNull($this->store->get('test'));
    }
}
