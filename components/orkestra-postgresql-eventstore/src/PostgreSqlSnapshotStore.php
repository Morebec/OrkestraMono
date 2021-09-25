<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use JsonException;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\Snapshot\Snapshot;
use Morebec\Orkestra\EventSourcing\Snapshot\SnapshotStoreInterface;
use RuntimeException;

class PostgreSqlSnapshotStore implements SnapshotStoreInterface
{
    private const STREAM_ID_COLUMN = 'stream_id';
    private const STREAM_VERSION_COLUMN = 'stream_version';
    private const STATE_COLUMN = 'state_data';
    private const TAKEN_AT = 'taken_at';
    private const SEQUENCE_NUMBER_COLUMN = 'sequence_number';

    private Connection $connection;
    private PostgreSqlSnapshotStoreConfiguration $configuration;
    private ClockInterface $clock;

    /**
     * @throws Exception
     * @throws SchemaException
     */
    public function __construct(
        Connection $connection,
        PostgreSqlSnapshotStoreConfiguration $configuration,
        ClockInterface $clock
    ) {
        if (!\extension_loaded('pdo_pgsql')) {
            throw new RuntimeException('Extension not loaded: "pdo_pgsql"');
        }

        $this->connection = $connection;
        $this->configuration = $configuration;

        $this->setupSchema($configuration);
        $this->clock = $clock;
    }

    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * @throws Exception
     */
    public function upsert(Snapshot $snapshot): void
    {
        $streamIdCol = self::STREAM_ID_COLUMN;
        $streamVersionCol = self::STREAM_VERSION_COLUMN;
        $sequenceNumberCol = self::SEQUENCE_NUMBER_COLUMN;
        $stateCol = self::STATE_COLUMN;
        $takenAtCol = self::TAKEN_AT;

        $takenAt = $this->clock->now();

        $sql = <<<SQL
            INSERT INTO {$this->configuration->snapshotsTableName} ({$streamIdCol}, {$streamVersionCol}, {$sequenceNumberCol}, {$stateCol}, {$takenAtCol})
                VALUES (:streamId, :streamVersion, :sequenceNumber, :state, :takenAt)
                ON CONFLICT ({$streamIdCol})
                DO
                    UPDATE SET {$streamVersionCol} = :streamVersion,
                               {$sequenceNumberCol} = :sequenceNumber,
                               {$stateCol} = :state,
                               {$takenAtCol} = :takenAt
            ;
            SQL;
        $this->connection->executeStatement($sql, [
            'streamId' => (string) $snapshot->getEventStreamId(),
            'streamVersion' => $snapshot->getStreamVersion()->toInt(),
            'state' => json_encode($snapshot->getState(), \JSON_THROW_ON_ERROR),
            'sequenceNumber' => $snapshot->getSequenceNumber()->toInt(),
            'takenAt' => $takenAt,
        ]);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function findByStreamId(EventStreamId $eventStreamId): ?Snapshot
    {
        $qb = $this->connection->createQueryBuilder();
        $qb = $qb->select('*')
            ->from($this->configuration->snapshotsTableName)
            ->where(sprintf('%s = %s', self::STREAM_ID_COLUMN, $qb->createPositionalParameter((string) $eventStreamId)))
        ;

        $result = $qb->executeQuery();
        $data = $result->fetchAssociative();

        return $data ? new Snapshot(
            $eventStreamId,
            EventStreamVersion::fromInt($data[self::STREAM_VERSION_COLUMN]),
            EventSequenceNumber::fromInt($data[self::SEQUENCE_NUMBER_COLUMN]),
            json_decode($data[self::STATE_COLUMN], true, 512, \JSON_THROW_ON_ERROR)
        ) : null;
    }

    /**
     * Clears the snapshot store.
     *
     * @throws Exception
     */
    public function clear(): void
    {
        /** @var PostgreSQL100Platform $platform */
        $platform = $this->connection->getDatabasePlatform();
        $this->connection->executeQuery($platform->getTruncateTableSQL($this->configuration->snapshotsTableName));
    }

    /**
     * @throws Exception
     * @throws SchemaException
     */
    private function setupSchema(PostgreSqlSnapshotStoreConfiguration $configuration): void
    {
        $schema = new Schema();

        $sm = $this->connection->createSchemaManager();

        if (!$sm->tablesExist($configuration->snapshotsTableName)) {
            $snapshotsTable = $schema->createTable($configuration->snapshotsTableName);
            $snapshotsTable->addColumn(self::STREAM_ID_COLUMN, 'string', ['notnull' => true]);
            $snapshotsTable->addUniqueIndex([self::STREAM_ID_COLUMN]);

            $snapshotsTable->addColumn(self::STREAM_VERSION_COLUMN, 'integer', ['notnull' => true]);
            $snapshotsTable->addColumn(self::SEQUENCE_NUMBER_COLUMN, 'integer', ['notnull' => true]);

            $snapshotsTable->addColumn(self::STATE_COLUMN, 'json');

            $snapshotsTable->addColumn(self::TAKEN_AT, 'datetime');
        }

        $queries = $schema->toSql($this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            // Since DBAL does not allow specifying JSONB, but only JSON, we specify it here.
            $query = str_replace('JSON', 'JSONB', $query);
            $this->connection->executeQuery($query);
        }
    }
}
