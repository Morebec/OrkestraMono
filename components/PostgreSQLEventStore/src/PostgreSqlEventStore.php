<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use InvalidArgumentException;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\CannotAppendToVirtualStreamException;
use Morebec\Orkestra\EventSourcing\EventStore\ConcurrencyException;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptorInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventMetadata;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStream;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamNotFoundException;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\MutableEventData;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamDirection;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\StreamedEventCollection;
use Morebec\Orkestra\EventSourcing\EventStore\StreamedEventCollectionInterface;
use Morebec\Orkestra\EventSourcing\EventStore\SubscriptionOptions;
use PDO;
use RuntimeException;

/**
 * Event Store implemented using storage to PostgreSQL.
 */
class PostgreSqlEventStore implements EventStoreInterface
{
    public const EVENT_STORE_IDENTIFIER = 'orkestra_pgsql';

    public const EVENT_STORE_VERSION = '2.0';

    public const GLOBAL_STREAM_ID = '$all';

    private const DIRECTION_FORWARD = 'ASC';

    private const DIRECTION_BACKWARD = 'DESC';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PostgreSqlEventStoreConfiguration
     */
    private $configuration;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var PostgreSqlSubscriberWrapper[]
     */
    private $subscribers;

    public function __construct(Connection $connection, PostgreSqlEventStoreConfiguration $configuration, ?ClockInterface $clock = null)
    {
        if (!\extension_loaded('pdo_pgsql')) {
            throw new RuntimeException('Extension not loaded: "pdo_pgsql"');
        }

        if (!$clock) {
            $clock = new SystemClock();
        }

        $this->clock = $clock;

        $this->connection = $connection;

        $this->configuration = $configuration;

        $this->setupSchema($configuration);

        $this->subscribers = [];
    }

    public function __destruct()
    {
        $this->connection->executeStatement("UNLISTEN {$this->configuration->eventsTableName}");
        $this->connection->close();
    }

    public function getGlobalStreamId(): EventStreamId
    {
        return EventStreamId::fromString(self::GLOBAL_STREAM_ID);
    }

    /**
     * Setups the schema of the database (if required) to support an event store.
     * There are only two tables required:
     * - The event log table containing all the events
     * - The streams table that other SQL implementations of an event store sometime refers
     * to as the aggregates table, is only used as a denormalization of streams in order, to speed up the process of querying the version of a stream.
     * The implementation could survive without it, but it is much faster to use this denormalization instead.
     *
     * @throws Exception
     * @throws SchemaException
     */
    public function setupSchema(PostgreSqlEventStoreConfiguration $configuration)
    {
        $schema = new Schema();

        // Events Table
        $sm = $this->connection->getSchemaManager();
        if (!$sm->tablesExist($configuration->eventsTableName)) {
            $eventsTable = $schema->createTable($configuration->eventsTableName);
            $eventsTable->addColumn(EventsTableKeys::ID, 'string', ['notnull' => true]);
            $eventsTable->setPrimaryKey([EventsTableKeys::ID]);

            $eventsTable->addColumn(EventsTableKeys::STREAM_ID, 'string', ['notnull' => true]);
            $eventsTable->addColumn(EventsTableKeys::STREAM_VERSION, 'integer', ['notnull' => true]);

            $eventsTable->addIndex([EventsTableKeys::STREAM_ID]);
            $eventsTable->addIndex([EventsTableKeys::STREAM_VERSION]);

            $eventsTable->addColumn(EventsTableKeys::TYPE, 'string');
            $eventsTable->addColumn(EventsTableKeys::METADATA, 'json');
            $eventsTable->addColumn(EventsTableKeys::DATA, 'json');
            $eventsTable->addColumn(EventsTableKeys::RECORDED_AT, 'datetime');

            $eventsTable->addColumn(EventsTableKeys::SEQUENCE_NUMBER, 'integer', ['autoincrement' => true]);
            $eventsTable->addIndex([EventsTableKeys::SEQUENCE_NUMBER]);
        }

        // Stream Table
        if (!$sm->tablesExist($configuration->streamsTableName)) {
            $streamsTable = $schema->createTable($configuration->streamsTableName);
            $streamsTable->addColumn(StreamsTableKeys::ID, 'string');
            $streamsTable->setPrimaryKey([StreamsTableKeys::ID]);
            $streamsTable->addColumn(StreamsTableKeys::VERSION, 'integer', ['notnull' => true, 'default' => 0]);
        }

        $queries = $schema->toSql($this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            // Since DBAL does not allow to specify JSONB, but only JSON, we specify it here.
            $query = str_replace('JSON', 'JSONB', $query);
            $this->connection->executeQuery($query);
        }

        $this->connection->executeStatement(<<<SQL
            LOCK TABLE {$configuration->eventsTableName};
            -- Create the trigger function
            CREATE OR REPLACE FUNCTION notify_{$configuration->eventsTableName}() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify('{$configuration->eventsTableName}', row_to_json(NEW)::text);
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;

            -- Create the trigger
            DROP TRIGGER IF EXISTS notify_{$configuration->eventsTableName}_trigger ON {$configuration->eventsTableName};
            CREATE TRIGGER notify_{$configuration->eventsTableName}_trigger
            AFTER INSERT
            ON {$configuration->eventsTableName}
            FOR EACH ROW EXECUTE PROCEDURE notify_{$configuration->eventsTableName}();
            SQL);

        // Start listening
        $this->connection->executeStatement("LISTEN {$this->configuration->eventsTableName}");
    }

    public function appendToStream(EventStreamId $streamId, iterable $eventDescriptors, AppendStreamOptions $options): void
    {
        // Make sure it is not a virtual stream.
        if ($streamId === $this->getGlobalStreamId()) {
            throw new CannotAppendToVirtualStreamException($streamId);
        }

        if (!$eventDescriptors) {
            return;
        }

        // Ensure all $eventDescriptors are instances of EventDescriptorInterface.
        foreach ($eventDescriptors as $descriptor) {
            if (!($descriptor instanceof EventDescriptorInterface)) {
                $expectedType = EventDescriptorInterface::class;
                $message = sprintf('Invalid argument, expected "%s", got "%s"', $expectedType, get_debug_type($descriptor));
                throw new InvalidArgumentException($message);
            }
        }

        $stream = $this->getStream($streamId);
        $streamVersion = $stream ? $stream->getVersion() : EventStreamVersion::initial();

        // Check concurrency
        if ($options->expectedStreamVersion && !$streamVersion->isEqualTo($options->expectedStreamVersion)) {
            throw new ConcurrencyException($streamId, $options->expectedStreamVersion, $streamVersion);
        }

        $versionAccumulator = $streamVersion->toInt();

        $eventDocuments = [];

        /** @var EventDescriptorInterface $descriptor */
        foreach ($eventDescriptors as $descriptor) {
            $versionAccumulator++;

            // Add recorded at metadata.
            $metadata = new MutableEventData($descriptor->getEventMetadata()->toArray());

            $recordedAt = $this->clock->now();
            $metadata->putValue('recordedAt', $recordedAt);

            $metadata->putValue('event_store', [
                'id' => self::EVENT_STORE_IDENTIFIER,
                'version' => self::EVENT_STORE_VERSION,
            ]);

            $eventData = $descriptor->getEventData();
            $eventDocuments[] = [
                EventsTableKeys::ID => (string) $descriptor->getEventId(),
                EventsTableKeys::STREAM_ID => (string) $streamId,
                EventsTableKeys::STREAM_VERSION => $versionAccumulator,
                EventsTableKeys::METADATA => json_encode($metadata),
                EventsTableKeys::TYPE => (string) $descriptor->getEventType(),
                EventsTableKeys::DATA => json_encode($eventData->toArray()),
                EventsTableKeys::RECORDED_AT => $recordedAt,
            ];
        }

        if (!$eventDocuments) {
            return;
        }

        $eventsTableName = $this->configuration->eventsTableName;
        $store = $this;

        $transactionFun = function (Connection $connection) use ($eventDocuments, $streamId, $streamVersion, $versionAccumulator) {
            if (!$this->streamExists($streamId)) {
                $this->createStream(new EventStream($streamId, $streamVersion));
            }

            foreach ($eventDocuments as $eventDocument) {
                $connection->insert($this->configuration->eventsTableName, $eventDocument);
            }

            // Update stream version index
            $this->updateStreamVersion($streamId, EventStreamVersion::fromInt($versionAccumulator));
        };
        $transactionFun = $transactionFun->bindTo($this);

        $this->connection->transactional($transactionFun);
    }

    /**
     * @throws Exception
     */
    public function readStream(EventstreamId $streamId, ReadStreamOptions $options): StreamedEventCollectionInterface
    {
        $isGlobalStream = $streamId->isEqualTo(self::getGlobalStreamId());

        if (!$isGlobalStream && !$this->streamExists($streamId)) {
            throw new EventStreamNotFoundException($streamId);
        }

        $direction = $options->direction->isEqualTo(ReadStreamDirection::FORWARD()) ? self::DIRECTION_FORWARD : self::DIRECTION_BACKWARD;

        $qb = $this->connection->createQueryBuilder();
        $qb = $qb->select('*')
            ->from($this->configuration->eventsTableName)
            ->orderBy(EventsTableKeys::SEQUENCE_NUMBER, $direction)
        ;

        // From position.
        $position = $options->position;
        $positionColumn = $isGlobalStream ? EventsTableKeys::SEQUENCE_NUMBER : EventsTableKeys::STREAM_VERSION;

        if ($position === ReadStreamOptions::POSITION_END) {
            $mQb = $this->connection->createQueryBuilder()
                ->select('MAX('.$positionColumn.')')
                ->from($this->configuration->eventsTableName)
            ;
            if (!$isGlobalStream) {
                $mQb->where(sprintf('%s = %s', EventsTableKeys::STREAM_ID, $mQb->createPositionalParameter((string) $streamId)));
            }

            $result = $mQb->execute();
            $position = $result->fetchAssociative()['max'] + 1;
        }

        $qb
            ->where(sprintf(
                '%s %s %s',
                $positionColumn,
                $direction === self::DIRECTION_FORWARD ? '>' : '<',
                $qb->createPositionalParameter($position)
            ))
        ;

        // Filter by stream (?)
        if (!$isGlobalStream) {
            $qb->andWhere(sprintf('%s = %s', EventsTableKeys::STREAM_ID, $qb->createPositionalParameter((string) $streamId)));
        }

        // Maximum count (?)
        if ($options->maxCount) {
            $qb
                ->setFirstResult(0)
                ->setMaxResults($options->maxCount)
            ;
        }

        // Run Query.
        $result = $qb->execute();
        $events = [];
        while ($queryData = $result->fetchAssociative()) {
            $queryData[EventsTableKeys::DATA] = json_decode($queryData[EventsTableKeys::DATA], true);
            $queryData[EventsTableKeys::METADATA] = json_decode($queryData[EventsTableKeys::METADATA], true);

            $events[] = new RecordedEventDescriptor(
                EventId::fromString($queryData[EventsTableKeys::ID]),
                EventType::fromString($queryData[EventsTableKeys::TYPE]),
                new EventMetadata($queryData[EventsTableKeys::METADATA]),
                new EventData($queryData[EventsTableKeys::DATA]),
                EventStreamId::fromString($queryData[EventsTableKeys::STREAM_ID]),
                EventStreamVersion::fromInt($queryData[EventsTableKeys::STREAM_VERSION]),
                EventSequenceNumber::fromInt($queryData[EventsTableKeys::SEQUENCE_NUMBER]),
                new DateTime($queryData[EventsTableKeys::RECORDED_AT])
            );
        }

        return new StreamedEventCollection($streamId, $events);
    }

    public function getStream(EventStreamId $streamId): ?EventStreamInterface
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from($this->configuration->streamsTableName)
            ->where(StreamsTableKeys::ID.' = '.$qb->createPositionalParameter((string) $streamId))
        ;

        $result = $qb->execute();
        $streamData = $result->fetchAssociative();

        return $streamData ? new EventStream($streamId, EventStreamVersion::fromInt($streamData[StreamsTableKeys::VERSION])) : null;
    }

    public function streamExists(EventStreamId $streamId): bool
    {
        return $this->getStream($streamId) !== null;
    }

    /**
     * Clears the whole storage.
     *
     * @throws Exception
     */
    public function clear(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->connection->executeQuery($platform->getTruncateTableSQL($this->configuration->eventsTableName));
        $this->connection->executeQuery($platform->getTruncateTableSQL($this->configuration->streamsTableName));
    }

    /**
     * Drops all tables of the event store.
     *
     * @throws Exception
     */
    public function dropTables(): void
    {
        $sm = $this->connection->getSchemaManager();
        $sm->dropTable($this->configuration->eventsTableName);
        $sm->dropTable($this->configuration->streamsTableName);
    }

    public function subscribeToStream(EventStreamId $streamId, EventStoreSubscriberInterface $subscriber): void
    {
        $this->subscribers[] = new PostgreSqlSubscriberWrapper($subscriber);
        // Catchup if required
        $subscriptionOptions = $subscriber->getOptions();
        if ($subscriptionOptions->position !== SubscriptionOptions::POSITION_END) {
            $events = $this->readStream($streamId, ReadStreamOptions::read()->forward()->maxCount(0)->from($subscriptionOptions->position));
            foreach ($events as $event) {
                $subscriber->onEvent($this, $event);
            }
        }
    }

    /**
     * Listens for new events.
     * Given the implementation of PDO, there is no way to have this run completely asynchronously.
     * This method needs to be called in an outer while loop to simulate real-time notifications.
     *
     * @throws Exception
     */
    public function notifySubscribers(): void
    {
        /** @var \Doctrine\DBAL\Driver\PDO\Connection $pdoConnection */
        $pdoConnection = $this->connection->getWrappedConnection();

        /** @var PDO $pgSqlConnection */
        $pgSqlConnection = $pdoConnection->getWrappedConnection();

        if ($data = $pgSqlConnection->pgsqlGetNotify(PDO::FETCH_ASSOC, $this->configuration->notifyTimeout)) {
            $data = json_decode($data['payload'], true);

            $descriptor = new RecordedEventDescriptor(
                EventId::fromString($data[EventsTableKeys::ID]),
                EventType::fromString($data[EventsTableKeys::TYPE]),
                new EventMetadata($data[EventsTableKeys::METADATA]),
                new EventData($data[EventsTableKeys::DATA]),
                EventStreamId::fromString($data[EventsTableKeys::STREAM_ID]),
                EventStreamVersion::fromInt($data[EventsTableKeys::STREAM_VERSION]),
                EventSequenceNumber::fromInt($data[EventsTableKeys::SEQUENCE_NUMBER]),
                new DateTime($data[EventsTableKeys::RECORDED_AT])
            );

            foreach ($this->subscribers as $subscriber) {
                $subscriber->onEvent($this, $descriptor);
            }
        }
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getConfiguration(): PostgreSqlEventStoreConfiguration
    {
        return $this->configuration;
    }

    /**
     * Creates a stream. Must not exist.
     *
     * @throws Exception
     */
    private function createStream(EventStream $stream): void
    {
        $this->connection->insert($this->configuration->streamsTableName, [
            StreamsTableKeys::ID => (string) $stream->getId(),
            StreamsTableKeys::VERSION => $stream->getVersion()->toInt(),
        ]);
    }

    private function updateStreamVersion(EventStreamId $streamId, EventStreamVersion $version): void
    {
        $this->connection->update(
            $this->configuration->streamsTableName,
            [StreamsTableKeys::VERSION => $version->toInt()],
            [StreamsTableKeys::ID => (string) $streamId]
        );
    }
}
