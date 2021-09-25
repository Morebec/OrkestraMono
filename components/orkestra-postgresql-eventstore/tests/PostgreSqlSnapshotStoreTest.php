<?php

namespace Tests\Morebec\Orkestra\PostgreSqlEventStore;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\Snapshot\Snapshot;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlSnapshotStore;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlSnapshotStoreConfiguration;
use PHPUnit\Framework\TestCase;

class PostgreSqlSnapshotStoreTest extends TestCase
{
    private PostgreSqlSnapshotStore $store;

    protected function setUp(): void
    {
        $config = new PostgreSqlSnapshotStoreConfiguration();

        $connection = DriverManager::getConnection([
            'url' => 'pgsql://postgres@localhost:5432/postgres?charset=UTF8',
        ], new Configuration());
        $this->store = new PostgreSqlSnapshotStore($connection, $config, new SystemClock());
        $this->store->clear();
    }

    /**
     * @throws Exception
     */
    public function testUpsert(): void
    {
        $eventStreamId = EventStreamId::fromString('unit_test');

        // INSERT
        $snapshot = new Snapshot(
            $eventStreamId,
            EventStreamVersion::fromInt(50),
            EventSequenceNumber::fromInt(100),
            [
                'hello' => 'world',
            ]
        );
        $this->store->upsert($snapshot);

        $found = $this->store->findByStreamId($eventStreamId);

        self::assertEquals($snapshot, $found);

        // UPDATE
        $snapshot = new Snapshot(
            $eventStreamId,
            EventStreamVersion::fromInt(55),
            EventSequenceNumber::fromInt(105),
            [
                'hello' => 'world_updated',
            ]
        );
        $this->store->upsert($snapshot);

        $found = $this->store->findByStreamId($eventStreamId);

        self::assertEquals($snapshot, $found);
    }

    public function testFindByStreamId(): void
    {
    }
}
