# PostgreSQLDocumentStore
Implementation of a Document Store using PostgreSQL's JSONB features.
It is based on `doctrine/dbal` for accessing the database internally.

## Installation
```shell
composer require morebec/orkestra-postgresql-document-store
```

## Usage
The Document Store uses `doctrine/dbal` for accessing the database.
Therefore, it requires a DBAL Connection as a constructor dependency.
It also relies on a `ClockInterface` from the `morebec/orkestra-date-time`component in order to access
the current date time.

```php
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStoreConfiguration;


$connection = DriverManager::getConnection([
    'url' => '...'
], new Configuration()); 

$config = new PostgreSqlDocumentStoreConfiguration(); 
$clock = new SystemClock();
$store = new PostgreSqlDocumentStore($connection, $config, $clock);
```

The second parameter corresponds to the configuration of the DocumentStore. This configuration class
can be used to alter the behaviour of the document store.


### Inserting Documents
To insert a document in a collection:
```php
/** @var Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore $store */
$store->insertDocument('users', 'usr123456789', [
    'id' => 'usr123456789',
    'username' => 'jane.doe',
    'fullname' => 'Jane Doe',
    'emailAddress' => 'jane.doe@email.com'
]);
```

If the collection does not exist, it will be created automatically.

### Finding Documents
Finding elements is done throughout the `findOneDocument` and `findManyDocuments` methods of the
document store. This method accepts either a string representing a postgresql json query or a `Filter`
which is a simple API for a query builder with the document store:

```php
use Morebec\Orkestra\PostgreSqlDocumentStore\Filter\Filter;
use Morebec\Orkestra\PostgreSqlDocumentStore\Filter\FilterOperator;

$store->insertDocument('users', 'usr123456789', [
    'id' => 'usr123456789',
    'username' => 'jane.doe',
    'fullname' => 'Jane Doe',
    'emailAddress' => 'jane.doe@email.com',
    'preferredLanguage' => 'ENGLISH' 
]);


// Finds a document by its ID.
$store->findOneDocument('users', Filter::findById('usr123456789'));

// Finds a document by a single field:
$store->findOneDocument('users', Filter::findByField('username', FilterOperator::EQUAL(), 'jane.doe'));

// Finds a document by a multiple criteria
$store->findOneDocument('users', 
    Filter::where('username', FilterOperator::EQUAL(), 'jane.doe')
    ->or('preferredLanguage', FilterOperator::IS_NOT(), null)
);


// You can also use strings to have greater control over the query:
$store->findOneDocument('users', 'data->>fullname = \'Jane Doe\'');
```
> If you are using the `Filter` query builder, the values are automatically escaped using prepared statements placeholders.
> However if you are using a string for a query, the values will not be escaped, and you must make sure that you are not introducing potential loopholes
> for SQL Injections.

> Internally a column `data` with type `JSONB` is added to every created collection table.
> This is why if you are doing a string query, you must specify the `data` column.

> For even greater control, the document store exposes a `getConnection` method which returns the `DBAL` connection
> which you can use to make more complex queries using doctrine's Query Builder.

### Updating Documents
To update a document, use the `updateDocument` method.
This method does not support partial documents, and therefore overwrites the document in the store
with the provided one:

```php
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore;

/** @var $store  PostgreSqlDocumentStore **/
$store->updateDocument('users', 'usr123456789', [
    'id' => 'usr123456789',
    'username' => 'jane.doe',
    'fullname' => 'Jane A. Doe',
    'emailAddress' => 'new.jane.doe@email.com',
    'preferredLanguage' => 'FRENCH' 
]);
```
### Removing Documents
Removing a document can be done as follows:
```php
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore;

/** @var $store  PostgreSqlDocumentStore **/
$store->removeDocument('users', 'usr123456789');
```

### Changing table names prefix.
In order to have better control over the collection tables it manages,
the document store adds a prefix to any table that it creates.

This prefix can be configured in the document store configuration:

```php
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStoreConfiguration;

$config = new PostgreSqlDocumentStoreConfiguration();

$config->collectionPrefix = 'you_prefix_';
```

### Transaction Management
If you need to use transactions for your operations, you can do this by accessing the DBAL connection:

```php
$connection = $store->getConnection();

$connection->transactional(static function() use ($store) {
    $store->insertDocument('users', 'usr123456789', [
        'id' => 'usr123456789',
        'username' => 'jane.doe',
        'fullname' => 'Jane Doe',
        'emailAddress' => 'jane.doe@email.com',
        'preferredLanguage' => 'ENGLISH' 
    ]);
    
    $store->insertDocument('users', 'usrABCDEFGHI', [
        'id' => 'usrABCDEFGHI',
        'username' => 'john.doe',
        'fullname' => 'John Doe',
        'emailAddress' => 'john.doe@email.com',
        'preferredLanguage' => 'SPANISH' 
    ]);
});
```


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