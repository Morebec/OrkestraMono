<?php

namespace Morebec\Orkestra\PostgreSqlTimeoutStorage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutStorageInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutWrapper;

/**
 * Implementation of a {@link TimeoutStorageInterface} using PostgreSQL.
 */
class PostgreSqlTimeoutStorage implements TimeoutStorageInterface
{
    public const ID_KEY = 'id';
    public const END_AT_KEY = 'end_at';
    public const MESSAGE_TYPE_NAME_KEY = 'message_type_name';
    public const MESSAGE_PAYLOAD_KEY = 'message_payload';
    public const MESSAGE_HEADERS = 'message_headers';

    private Connection $connection;

    private PostgreSqlTimeoutStorageConfiguration $configuration;

    private MessageNormalizerInterface $messageNormalizer;

    public function __construct(
        Connection $connection,
        PostgreSqlTimeoutStorageConfiguration $configuration,
        MessageNormalizerInterface $messageNormalizer
    ) {
        if (!\extension_loaded('pdo_pgsql')) {
            throw new \RuntimeException('Extension not loaded: "pdo_pgsql"');
        }

        $this->connection = $connection;

        $this->configuration = $configuration;

        $this->setupSchema($configuration);
        $this->messageNormalizer = $messageNormalizer;
    }

    public function add(TimeoutWrapper $wrapper): void
    {
        $timeout = $wrapper->getTimeout();
        $headers = $wrapper->getMessageHeaders();

        $this->connection->insert($this->configuration->timeoutTableName, [
            self::ID_KEY => $timeout->getId(),
            self::END_AT_KEY => $timeout->getEndsAt(),
            self::MESSAGE_TYPE_NAME_KEY => $timeout::getTypeName(),
            self::MESSAGE_PAYLOAD_KEY => json_encode($this->messageNormalizer->normalize($timeout), \JSON_THROW_ON_ERROR),
            self::MESSAGE_HEADERS => json_encode($headers->toArray(), \JSON_THROW_ON_ERROR),
        ]);
    }

    public function findByEndsAtBefore(DateTime $dateTime): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from($this->configuration->timeoutTableName)
            ->where(self::END_AT_KEY.' <= '.$qb->createPositionalParameter($dateTime))
        ;
        $result = $qb->executeQuery();

        return $this->convertDbResultToTimeoutWrappers($result);
    }

    public function findByEndsAtBetween(DateTime $from, DateTime $to): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from($this->configuration->timeoutTableName)
            ->where(self::END_AT_KEY.' >= '.$qb->createPositionalParameter($from))
            ->andWhere(self::END_AT_KEY.' <= '.$qb->createPositionalParameter($to))
        ;
        $result = $qb->executeQuery();

        return $this->convertDbResultToTimeoutWrappers($result);
    }

    public function remove(string $timeoutId): void
    {
        $this->connection->delete($this->configuration->timeoutTableName, [
            self::ID_KEY => $timeoutId,
        ]);
    }

    /**
     * @throws Exception
     */
    public function clear(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->connection->executeQuery($platform->getTruncateTableSQL($this->configuration->timeoutTableName));
    }

    public function convertDbResultToTimeoutWrappers(Result $result): array
    {
        $timeouts = [];
        while ($data = $result->fetchAssociative()) {
            /** @var string $messageTypeName */
            $messageTypeName = $data[self::MESSAGE_TYPE_NAME_KEY];
            $payload = json_decode($data[self::MESSAGE_PAYLOAD_KEY], true, 512, \JSON_THROW_ON_ERROR);
            $headersData = json_decode($data[self::MESSAGE_HEADERS], true, 512, \JSON_THROW_ON_ERROR);

            /** @var TimeoutInterface $timeout */
            $timeout = $this->messageNormalizer->denormalize($payload, $messageTypeName);
            $headers = new MessageHeaders($headersData);

            $timeouts[] = TimeoutWrapper::wrap($timeout, $headers);
        }

        return $timeouts;
    }

    private function setupSchema(PostgreSqlTimeoutStorageConfiguration $configuration): void
    {
        $schema = new Schema();

        $sm = $this->connection->createSchemaManager();
        if (!$sm->tablesExist($configuration->timeoutTableName)) {
            $eventsTable = $schema->createTable($configuration->timeoutTableName);
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
