<?php

namespace Morebec\Orkestra\OrkestraServer\Core\DependencyInjection;

use Doctrine\DBAL\Connection;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStoreConfiguration;

class PostgreSqlDocumentStoreFactory
{
    /**
     * @var ClockInterface
     */
    private $clock;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, ClockInterface $clock)
    {
        $this->clock = $clock;
        $this->connection = $connection;
    }

    public function create(): PostgreSqlDocumentStore
    {
        $config = new PostgreSqlDocumentStoreConfiguration();

        return new PostgreSqlDocumentStore($this->connection, $config, $this->clock);
    }
}
