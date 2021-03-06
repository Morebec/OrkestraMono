# PostgreSQL Timeout Storage
This component is an implementation of a TimeoutStorage from the [Messaging Component]()
using PostgreSQL.

## Installation

```shell
composer require orkestra-orkestra-postgresql-timeout-storage
```

## Usage

```php

use Morebec\Orkestra\PostgreSqlTimeoutStorage\PostgreSqlTimeoutStorage;
use Morebec\Orkestra\PostgreSqlTimeoutStorage\PostgreSqlTimeoutStorageConfiguration;

$connection = new DriverManaer([
    'url' => '...'
], new Configuration());

$configuration = new PostgreSqlTimeoutStorageConfiguration();
$storage = new PostgreSqlTimeoutStorage($connection, $configuration);
```

### Configuration
The `PostgreSqlTimeoutStorageConfiguration` class is used to configure the behavior of the timeout storage.
You can for example change the polling interval according to the needs of the application developed.

> Usually PostgreSQL is capable of handling polling efficiently, but of course, it always adds
> a little more work on it. It is advised to set up the `pollingInterval` to the minimum frequency at which new Timeouts
> are added in your application.

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