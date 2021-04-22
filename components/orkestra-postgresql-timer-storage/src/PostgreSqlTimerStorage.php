<?php

namespace Morebec\Orkestra\PostgreSqlTimerStorage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Messaging\Timer\TimerInterface;
use Morebec\Orkestra\Messaging\Timer\TimerStorageInterface;
use Morebec\Orkestra\Messaging\Timer\TimerWrapper;
use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;

/**
 * Implementation of a {@link TimerStorageInterface} using PostgreSQL.
 */
class PostgreSqlTimerStorage implements TimerStorageInterface
{
    public const ID_KEY = 'id';
    public const END_AT_KEY = 'end_at';
    public const MESSAGE_TYPE_NAME_KEY = 'message_type_name';
    public const MESSAGE_PAYLOAD_KEY = 'message_payload';
    public const MESSAGE_HEADERS = 'message_headers';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PostgreSqlTimerStorageConfiguration
     */
    private $configuration;
    /**
     * @var MessageNormalizerInterface
     */
    private $messageNormalizer;
    /**
     * @var ObjectNormalizerInterface
     */
    private $objectNormalizer;

    public function __construct(
        Connection $connection,
        PostgreSqlTimerStorageConfiguration $configuration,
        MessageNormalizerInterface $messageNormalizer,
        ObjectNormalizerInterface $objectNormalizer
    ) {
        if (!\extension_loaded('pdo_pgsql')) {
            throw new \RuntimeException('Extension not loaded: "pdo_pgsql"');
        }

        $this->connection = $connection;

        $this->configuration = $configuration;

        $this->setupSchema($configuration);
        $this->messageNormalizer = $messageNormalizer;
        $this->objectNormalizer = $objectNormalizer;
    }

    public function add(TimerWrapper $wrapper): void
    {
        $timer = $wrapper->getTimer();
        $headers = $wrapper->getMessageHeaders();

        $this->connection->insert($this->configuration->timerTableName, [
            self::ID_KEY => $timer->getId(),
            self::END_AT_KEY => $timer->getEndsAt(),
            self::MESSAGE_TYPE_NAME_KEY => $timer::getTypeName(),
            self::MESSAGE_PAYLOAD_KEY => json_encode($this->messageNormalizer->normalize($timer)),
            self::MESSAGE_HEADERS => json_encode($headers->toArray()),
        ]);
    }

    public function findByEndsAtBefore(DateTime $dateTime): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from($this->configuration->timerTableName)
            ->where(self::END_AT_KEY.' <= '.$qb->createPositionalParameter($dateTime))
        ;
        $result = $qb->executeQuery();

        return $this->convertDbResultToTimerWrappers($result);
    }

    public function findByEndsAtBetween(DateTime $from, DateTime $to): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from($this->configuration->timerTableName)
            ->where(self::END_AT_KEY.' >= '.$qb->createPositionalParameter($from))
            ->andWhere(self::END_AT_KEY.' <= '.$qb->createPositionalParameter($to))
        ;
        $result = $qb->executeQuery();

        return $this->convertDbResultToTimerWrappers($result);
    }

    public function remove(string $timerId): void
    {
        $this->connection->delete($this->configuration->timerTableName, [
            self::ID_KEY => $timerId,
        ]);
    }

    /**
     * @throws Exception
     */
    public function clear(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->connection->executeQuery($platform->getTruncateTableSQL($this->configuration->timerTableName));
    }

    public function convertDbResultToTimerWrappers(Result $result): array
    {
        $timers = [];
        while ($data = $result->fetchAssociative()) {
            /** @var string $messageTypeName */
            $messageTypeName = $data[self::MESSAGE_TYPE_NAME_KEY];
            $payload = json_decode($data[self::MESSAGE_PAYLOAD_KEY], true);
            $headersData = json_decode($data[self::MESSAGE_HEADERS], true);

            /** @var TimerInterface $timer */
            $timer = $this->messageNormalizer->denormalize($payload, $messageTypeName);
            $headers = new MessageHeaders($headersData);

            $timers[] = TimerWrapper::wrap($timer, $headers);
        }

        return $timers;
    }

    private function setupSchema(PostgreSqlTimerStorageConfiguration $configuration): void
    {
        $schema = new Schema();

        $sm = $this->connection->createSchemaManager();
        if (!$sm->tablesExist($configuration->timerTableName)) {
            $eventsTable = $schema->createTable($configuration->timerTableName);
            $eventsTable->addColumn(self::ID_KEY, 'string', ['notnull' => true]);
            $eventsTable->setPrimaryKey([self::ID_KEY]);
            $eventsTable->addColumn(self::END_AT_KEY, 'datetime', ['notnull' => true]);
            $eventsTable->addColumn(self::MESSAGE_PAYLOAD_KEY, 'json', ['notnull' => true]);
            $eventsTable->addColumn(self::MESSAGE_HEADERS, 'json', ['notnull' => true]);
            $eventsTable->addColumn(self::MESSAGE_TYPE_NAME_KEY, 'string', ['notnull' => true]);
        }

        $queries = $schema->toSql($this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            // Since DBAL does not allow to specify JSONB, but only JSON, we specify it here.
            $query = str_replace('JSON', 'JSONB', $query);
            $this->connection->executeQuery($query);
        }
    }
}
