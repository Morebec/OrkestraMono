# PostgreSQL Personal Information Store
This component is a PostgreSQL based implementation of the Orkestra Privacy Component's
Personal Information Store. It supports encrypting data in the personal store.

It relies on DBAL for communication with PostgreSQL.

## Installation
The component can be installed using composer.
```shell
composer require morebec/orkestra-postgresql-personal-information-store
```

## Usage
Create a new instance of a `PostgreSqlPersonalInformationStore`.
```php
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Morebec\Orkestra\PostgreSqlPersonalInformationStore\PostgreSqlPersonalInformationStore;
use Morebec\Orkestra\PostgreSqlPersonalInformationStore\PostgreSqlPersonalInformationStoreConfiguration;

$connection = DriverManager::getConnection([
    'url' => '...'
], new Configuration()); 

$config = new PostgreSqlPersonalInformationStoreConfiguration();
$config->encryptionKey = PostgreSqlPersonalInformationStore::generateEncryptionKey(); // Or random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
$store = new PostgreSqlPersonalInformationStore($connection,$config);
```

> Ensure you save the encryption key securely such as .env variables or a secret manager.

For more information for how to use the Store, see the documentation for the [Privacy Component]().

### Disposable Information
The information stored in the Personal Information Store can have a `disposedAt` value which indicates if and when 
the data should be destroyed.
This component ships a `DisposedPersonalDataRemover` service class that inspects the store for expired data and deletes it.

It should be used in a daemon:

```php
use Morebec\Orkestra\PostgreSqlPersonalInformationStore\DisposedPersonalDataRemover;

$remover = new DisposedPersonalDataRemover($store);

while(true) {
    $remover->run();
    sleep(60);
}
```

## Security
Given the goal of this store is to save Personal Data, the PostgreSQL server must be highly secured against attackers.
Here are a few ideas to help you get started.

### Client Authentication Control
Client Authentication Control allows to specify the way clients of the PostgreSQL server
can connect to it, and if they are allowed to. This configuration can be done in the `pg_hba.conf`: 

```conf
# TYPE  DATABASE        USER            ADDRESS                 METHOD
host     postgres       all             172.20.0.0/24           ident
host     all            all             0.0.0.0/0               reject
```

For example, the above config would only allow all clients with IP address  `172.20.0.x` to connect
to the server on the database postgres using the authentication method ident which works by using the client's
operating system username.
Given this file is read from top to bottom for a match, the last line would reject any other connection attempts. 

### Server Configuration
Another thing that can be done to enhance the security of the PostgreSQL server is of course to edit
its Server configuration.
For example:
- Changing the default port to something else,
- Changing the list of allowed client addresses (similar to Client Authentication Control).
- Using SSL

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