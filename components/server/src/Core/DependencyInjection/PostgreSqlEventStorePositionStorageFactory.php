<?php

namespace Morebec\Orkestra\OrkestraServer\Core\DependencyInjection;

use Doctrine\DBAL\Connection;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStorePositionStorage;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStorePositionStorageConfiguration;

class PostgreSqlEventStorePositionStorageFactory
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function create(): PostgreSqlEventStorePositionStorage
    {
        $configuration = new PostgreSqlEventStorePositionStorageConfiguration();

        return new PostgreSqlEventStorePositionStorage($this->connection, $configuration);
    }
}
