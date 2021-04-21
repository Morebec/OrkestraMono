# PostgreSqlEventStore
This component provides an implementation of a the an Event Store (from the [EventSourcing Component]())
using PostgreSQL.

Under the hood, it uses DBAL for communication with PostgreSQL.

## Installation
```shell
composer require morebec/orkestra-postgresql-eventstore
```

## Usage

```php
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStore;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStoreConfiguration;

$connection = DriverManager::getConnection([
    'url' => '...'
], new Configuration()); 

$config = new PostgreSqlEventStoreConfiguration();
$store = new PostgreSqlEventStore($connection, $config);

```

### Event Store Subscriptions
The pu/sub mechanism of the event store is implemented using PostgreSQL's `LISTEN/NOTIFY` feature.
Unfortunately given the nature of PHP being a synchronous RunTime, the only way to have Pub/Sub capabilities
is to run a `LISTEN` loop: 

```php
// This method will start a loop and listen for communications from PostgreSQL's LISTEN/NOTIFY mechanis.
$store->notifySubscribers();
```

This is can be used with `Event Processors`.

### PostgreSqlEventStorePositionStorage
An implementation of `EventStorePositionStorageInterface` is also shipped with the component as the `PostgreSqlEventStorePositionStorage`,
which also relies on DBAL for communication with PostgreSQL:

```php
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStorePositionStorage;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStorePositionStorageConfiguration;

$connection = DriverManager::getConnection([
    'url' => '...'
], new Configuration()); 

$config = new PostgreSqlEventStorePositionStorageConfiguration();
$store = new PostgreSqlEventStorePositionStorage($connection, $config);
```


### PostgreSqlEventProcessor
A ready-made implementation of an Event Processor with support for the `PostgreSqlEventStore`
is also provided with this component:

```php
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventProcessor;
$processor = new PostgreSqlEventProcessor($publisher, $eventStore, $postgreSqlEventStore, $positionStorage);

// This call will loop and notify all event store subscribers as well as the event processor itself
// for event tracking.
$processor->start();
```

> Given that the `EventStoreInterface` can be decorated, the processor cannot directly use the `PostgreSqlEventStore` through the `EventStoreInterface`.
> Also, since it requires using specific methods from the implementation `PostgreSqlEventStore`  to access some PostgreSQL specific features,
> the actual instance of the `PostgreSqlEventStore` must be injected additionally. 
> This is why both are injected in the constructor.

## Testing
To run the tests execute the following command:
```shell
vendor/bin/phpunit tests/
```

It is required to have an instance of postgresql running with a password-less role `postgres` and a database named `postgres`.
To easily get this setup and running a `docker-compose` configuration file is available at the root of this project.

To run it simply execute the following command:

```shell
docker-compose up -d
```