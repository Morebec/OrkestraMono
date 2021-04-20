<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventStorePositionStorageInterface;

class PostgreSqlEventStorePositionStorage implements EventStorePositionStorageInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;
    /**
     * @var PostgreSqlEventStorePositionStorageConfiguration
     */
    private $configuration;

    public function __construct(Connection $connection, PostgreSqlEventStorePositionStorageConfiguration $configuration)
    {
        if (!\extension_loaded('pdo_pgsql')) {
            throw new \RuntimeException('Extension not loaded: "pdo_pgsql"');
        }

        $this->connection = $connection;
        $this->configuration = $configuration;

        $this->setupSchema($configuration);
    }

    public function __destruct()
    {
        $this->connection->close();
    }

    public function set(string $processorId, ?int $position): void
    {
        $idKey = ProcessorPositionsTableKeys::ID;
        $positionKey = ProcessorPositionsTableKeys::POSITION;
        $sql = <<<SQL
            INSERT INTO {$this->configuration->positionsTableName} ({$idKey}, {$positionKey})
                VALUES (:id, :position)
                ON CONFLICT ({$idKey}) DO UPDATE SET {$positionKey} = :position ;
            SQL;
        $this->connection->executeStatement($sql, ['id' => $processorId, 'position' => $position]);
    }

    public function reset(string $processorId): void
    {
        $this->connection->delete($this->configuration->positionsTableName, [
            ProcessorPositionsTableKeys::ID => $processorId,
        ]);
    }

    public function get(string $processorId): ?int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(ProcessorPositionsTableKeys::POSITION)
            ->from($this->configuration->positionsTableName)
            ->where(ProcessorPositionsTableKeys::ID.' = '.$qb->createPositionalParameter($processorId))
        ;

        $result = $qb->execute();

        $value = $result->fetchOne();

        if ($value === false) {
            return null;
        }

        return $value;
    }

    public function clear(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->connection->executeQuery($platform->getTruncateTableSQL($this->configuration->positionsTableName));
    }

    private function setupSchema(PostgreSqlEventStorePositionStorageConfiguration $configuration)
    {
        $schema = new Schema();

        $sm = $this->connection->getSchemaManager();
        if (!$sm->tablesExist($configuration->positionsTableName)) {
            $eventsTable = $schema->createTable($configuration->positionsTableName);
            $eventsTable->addColumn(ProcessorPositionsTableKeys::ID, 'string', ['notnull' => true]);
            $eventsTable->setPrimaryKey([ProcessorPositionsTableKeys::ID]);

            $eventsTable->addColumn(ProcessorPositionsTableKeys::POSITION, 'string', ['notnull' => true]);
        }

        $queries = $schema->toSql($this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            $this->connection->executeQuery($query);
        }
    }
}
