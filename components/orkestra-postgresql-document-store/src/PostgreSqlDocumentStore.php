<?php

namespace Morebec\Orkestra\PostgreSqlDocumentStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\PostgreSqlDocumentStore\Filter\Filter;

class PostgreSqlDocumentStore
{
    public const ID_INDEX_NAME = 'primary';

    private PostgreSqlDocumentStoreConfiguration $config;

    private Connection $connection;

    private ClockInterface $clock;

    /**
     * orkestra-postgresql-document-store constructor.
     */
    public function __construct(Connection $connection, PostgreSqlDocumentStoreConfiguration $config, ClockInterface $clock)
    {
        $this->config = $config;
        $this->connection = $connection;
        $this->clock = $clock;
    }

    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * Creates a new collection explicitly.
     *
     * @throws SchemaException
     * @throws Exception
     */
    public function createCollection(string $collectionName): void
    {
        if ($this->hasCollection($collectionName)) {
            return;
        }

        $schema = new Schema();

        $table = $schema->createTable($this->prefixCollection($collectionName));
        $table->addColumn(CollectionTableColumnKeys::ID, 'string');
        $table->setPrimaryKey([CollectionTableColumnKeys::ID], $collectionName.'_'.self::ID_INDEX_NAME);

        $table->addColumn(CollectionTableColumnKeys::DATA, 'json');

        $table->addColumn(CollectionTableColumnKeys::CREATED_AT, 'datetime');
        $table->addColumn(CollectionTableColumnKeys::UPDATED_AT, 'datetime');

        $queries = $schema->toSql($this->connection->getDatabasePlatform());
        foreach ($queries as $query) {
            // Since DBAL does not allow to specify JSONB, but only JSON, we specify it here.
            $query = str_replace('JSON', 'JSONB', $query);
            $this->connection->executeQuery($query);
        }
    }

    /**
     * Deletes a collection and all its data.
     *
     * @throws Exception
     */
    public function dropCollection(string $collectionName): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $tableName = $this->prefixCollection($collectionName);

        if ($schemaManager->tablesExist($tableName)) {
            $schemaManager->dropTable($tableName);
            $indexes = $schemaManager->listTableIndexes($collectionName);
            foreach ($indexes as $index) {
                $this->connection->executeStatement("DROP INDEX {$index->getName()};");
            }
        }
    }

    /**
     * Renames a collection.
     *
     * @throws Exception
     */
    public function renameCollection(string $collectionName, string $newCollectionName): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $schemaManager->renameTable($this->prefixCollection($collectionName), $this->prefixCollection($newCollectionName));
    }

    /**
     * Lists all orkestra-collections by returning their name.
     *
     * @return string[]
     *
     * @throws Exception
     */
    public function listCollections(): array
    {
        $schemaManager = $this->connection->createSchemaManager();

        $prefix = $this->config->collectionPrefix;
        $tables = array_values(array_filter($schemaManager->listTables(), static function (Table $table) use ($prefix) {
            return strpos($table->getName(), $prefix) === 0;
        }));

        return array_map(static function (Table $table) use ($prefix) {
            return str_replace($prefix, '', $table->getName());
        }, $tables);
    }

    /**
     * Indicates if a collection exists or not.
     *
     * @throws Exception
     */
    public function hasCollection(string $collectionName): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist($this->prefixCollection($collectionName));
    }

    /**
     * Adds a new document to a collection.
     *
     * @throws Exception
     */
    public function insertDocument(string $collectionName, string $id, array $data): void
    {
        $this->createCollectionIfNotExists($collectionName);

        $this->connection->insert($this->prefixCollection($collectionName), [
                CollectionTableColumnKeys::ID => $id,
                CollectionTableColumnKeys::DATA => json_encode($data, \JSON_THROW_ON_ERROR),
                CollectionTableColumnKeys::CREATED_AT => $this->clock->now(),
                CollectionTableColumnKeys::UPDATED_AT => $this->clock->now(),
        ]);
    }

    /**
     * Updates a document of collection entirely.
     *
     * @throws Exception
     */
    public function updateDocument(string $collectionName, string $documentId, array $data): void
    {
        $this->connection->update($this->prefixCollection($collectionName), [
                CollectionTableColumnKeys::DATA => json_encode($data, \JSON_THROW_ON_ERROR),
                CollectionTableColumnKeys::UPDATED_AT => $this->clock->now(),
        ], [CollectionTableColumnKeys::ID => $documentId]);
    }

    /**
     * @param string|Filter $filter
     *
     * @throws Exception
     */
    public function findOneDocument(string $collectionName, $filter): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(CollectionTableColumnKeys::DATA)
            ->from($this->prefixCollection($collectionName))
        ;

        if ($filter instanceof Filter) {
            $configurator = new FilterQueryBuilderConfigurator();
            $qb = $configurator->configure($filter, $qb);
        } else {
            $qb->where($filter);
        }

        $result = $qb->executeQuery();
        $doc = $result->fetchAssociative();

        if (($doc === false)) {
            return null;
        }

        return json_decode($doc[CollectionTableColumnKeys::DATA], true, 512, \JSON_THROW_ON_ERROR);
    }

    /**
     * @param string|Filter $filter
     *
     * @throws Exception
     */
    public function findManyDocuments(string $collectionName, $filter): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(CollectionTableColumnKeys::DATA)
            ->from($this->prefixCollection($collectionName))
        ;

        if ($filter instanceof Filter) {
            $configurator = new FilterQueryBuilderConfigurator();
            $qb = $configurator->configure($filter, $qb);
        } else {
            $qb->where($filter);
        }

        $result = $qb->executeQuery();
        $documents = [];

        while ($doc = $result->fetchAssociative()) {
            $documents[] = json_decode($doc[CollectionTableColumnKeys::DATA], true, 512, \JSON_THROW_ON_ERROR);
        }

        return $documents;
    }

    /**
     * Finds all the documents in a given collection.
     */
    public function findAllDocuments(string $collectionName): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(CollectionTableColumnKeys::DATA)
            ->from($this->prefixCollection($collectionName))
        ;

        $result = $qb->executeQuery();
        $documents = [];

        while ($doc = $result->fetchAssociative()) {
            $documents[] = json_decode($doc[CollectionTableColumnKeys::DATA], true, 512, \JSON_THROW_ON_ERROR);
        }

        return $documents;
    }

    public function removeDocument(string $collectionName, string $id): void
    {
        $this->connection->delete($this->prefixCollection($collectionName), [CollectionTableColumnKeys::ID => $id]);
    }

    public function executeQuery(string $query): Result
    {
        return $this->connection->executeQuery($query);
    }

    /**
     * Clears the whole document store dropping all orkestra-collections.
     *
     * @throws Exception
     */
    public function clear(): void
    {
        $sm = $this->connection->createSchemaManager();
        $collections = $this->listCollections();
        foreach ($collections as $collection) {
            $this->dropCollection($collection);
        }
    }

    /**
     * Returns the DBAL connection.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Returns the prefixed name of a collection.
     */
    public function prefixCollection(string $collectionName): string
    {
        return "{$this->config->collectionPrefix}{$collectionName}";
    }

    /**
     * Ensures a collection exists, by creating it if does not exist.
     *
     * @throws Exception
     * @throws SchemaException
     */
    public function createCollectionIfNotExists(string $collectionName): void
    {
        if (!$this->hasCollection($collectionName)) {
            $this->createCollection($collectionName);
        }
    }
}
