# Privacy
The Privacy component proposes as set of interfaces to a `PersonalInformationStore` which can be used to centralise
all Personally Identifiable Information (PII) of a user as well as storing additional data such as the reasons, the processing
operations or the legal basis for storing such information.

It does not offer any implementation (other than an In Memory one for tests).

> For an actual implementation you can check out the official [PostgreSQLPersonalInformationStore]() which is a ready-made 
PostgreSQL implementation with support for encryption.

One of its primary goals is to provide a solution to Event Sourced Application to be able to remain immutable
while allowing to have forgettable personal data.

## Event Sourcing
One of the challenges regarding privacy regulation and Event Sourcing is that these privacy regulations allows
data subject to request to have their personal data removed from a system, where event sourced system are immutable in nature.

There are three common ways to solve this, with varying degrees of complexity and effectiveness:

### Mutable Event Store
One possible way is to have a mutable event store, i.e. have an implementation of an event store that can have some of its events deleted.
Using a RDBMS or Mongo Db based event store technically allows one to do this quite easily.
The downside of this is that the "audit for free" promise of event sourcing is no longer possible as data
can be tempered with on a conceptual level, and from within the application.

Another downside of this strategy, is that it requires careful manipulation as changing the "past"
might have hard to predict side effects and render the application unstable or unusable.


### Forgettable Event Payloads
One solution that tries to keep the event store immutable, is to avoid saving the data in the event store directly but instead, saving
references to that data in another store secured with encryption:

```javascript
UserRegistered
{
    username: personal-data/x54tufojs536pzeqhelshvd7
    fullname: personal-data/folshvd7js536pzeqhex54tu
    password: personal-data/x134s5do6pzxqhelshvd8f
}
```

The challenges this strategy brings, is that the consumers of the events that requires access to the raw data,
will need to query the Personal Information Store, which adds complexity and might have an impact on performance.

### Cryptographic Erasure
Cryptographic Erasure is a strategy where one encrypts the data before saving them in events
and then stores the decryption key in another storage. When a user invokes the right to be forgotten, the
decryption key is simply discarded, rendering the information obsolete and no longer accessible.
It uses a similar mindset to the forgettable payloads the difference being that the actual data 
is kept in the event store in an encrypted form.

It has the same challenges as the *Forgettable Event Payloads* strategy as well as additional ones
related to cryptography such as key rotation, or encryption weakening over time.
Indeed, if a value was encrypted with an algorithm that is discovered to be weak or ineffective in the future
the data could still be recoverable.

One additional thing to note is that it is still unclear whether this is a legal measure since the data is
not technically deleted as required by the GDPR for example:

Even in an encrypted form, personal information is still considered by the GDPR as Personal Data:
> 
> *"A confidentiality breach on personal data that were encrypted with a state-of-the-art algorithm is still a personal data breach, and has to be notified to the authority."*
 


### Summary
The privacy component can be used to support both the Mutable Event Store and Cryptographic erasure
as the Personal Information Store's API simply provides Find, Upsert and Delete operations.
From a technical point of view it is advised to use the "Forgettable Payload" strategy as opposed 
to the other two mentioned as it is the one that provides the most future-proof and compliant solution.

For the performance hits it could present, multiple strategies can be performed to minimize this.

## Installation

```shell
composer require morebec/orkestra-privacy
```

## Usage
In order to fully use the component you will need an implementation of the `PersonalInformationStoreInterface`.

The official [PostgreSQLPersonalInformationStore]() is a ready-made implementation with support for encryption.

The storage works with some core concepts:
- **Personal Data** represents a PII value such as a person's name, email address, phone number, birthdate etc as well as additional information regarding how the data was obtained, 
  for what purposes and for how long it should be retained.
- **Personal Token** represents a token that is used internally by the application to identify a given person. Such as an internal UUID, in other words the owner of Personal Data. 
  For GDPR compliance this value should not be natural key and easily disposable, i.e. it should not be used to identify the natural person after an erasure of their data.
  It serves as a namespace for a set of related values.
- **Reference Token** Represents a reference to some given Personal Data that can be used in the application to reference the data contained in the store.
- **Key** Corresponds to a key name associated with a piece personal data, e.g. email address, username, phone number etc.


### Adding Personal Data
To add personal data to the store, one must use the `PersonalData` class or an implementation of the `PersonalDataInterface` and add it to the store:

```php
use Morebec\Orkestra\Privacy\PersonalData;
 
$personalToken='usr123456';

$data = new PersonalData($personalToken, 'emailAddress' /* key */, 'jane.doe@email.com' /* value */, 'registration_form' /* source */);
$data->disposedAt($clock->now()->addDays(15));

// You can use this reference token to reference this personal data within the store in your application code.
$referenceToken = $store->put($data);
```

The value can be any PHP scalar primitive or array of scalar primitives.

> If an entry of Personal Data for the same `personal token` and `key` combination exists, it will be overwritten, 
> see the Updating Data section for more information.

> This method does not provide any idempotency guarantees meaning that
> consecutive calls to this method with the same personal data, might return
> different reference tokens.

### Retrieving Personal Data
The primary way of retrieving data is using the `reference token` of the data:
```php
$data = $store->findOneByReferenceToken($referenceToken);
```
The returned value is an instance of `RecordedPersonalData` which is an immutable data structure around personal data.

If the data does not exist, `null` will be returned.

It is also possible to query the personal store for a specific `key` of a `personal token`:

```php
// Data can also be retrieved in an number of ways:
$data = $store->findOneByKeyName('user123456', 'emailAddress'); 
```

One can also query by `personal token` to obtain all the related personal data present in the store: 
```php
// Returns an array of all personal data related to a personal token.
$data = $store->findByPersonalToken('user123456');

```

Again, if the data does not exist, `null` will be returned.


### Updating Data
Updating data can be performed simply by overwriting some personal data already existing data with the `put` method:

```php
use Morebec\Orkestra\Privacy\PersonalData;
$data = new PersonalData('usr123456', 'emailAddress', 'jane.doe123@email.com', 'account_settings');

// You can use this reference token to reference this personal data within the store.
$referenceToken = $store->put($data);
```

If the data did not exist, it will be equivalent to adding new data to the store.

> Even if the data already existed in the store, a new reference token will be returned.

### Removing Data
There are two different ways to remove data form the store.

The first one is by specifying the `personal token` and the `key` combination:
```php
$store->removeByKeyName('user123456', 'emailAddress');
```

The other way is by specifying only the personal token with the `erase` method:
```php
// Deletes all records for a given personal token.
$store->erase('user123456');
```

This will have for effect of removing all personal information related to that `personal token`

### Removing Disposable Data
The `PersonalDataInterface` has a value indicating the DateTime at which the data should be considered disposable.
This disposable nature is used in order not to store information indefinitely and without active use in the store.
To easily clean up the store from this expired data, this package contains a `DisposedPersonalDataRemoverInterface`:

```php
$remover->run(); // Will remove all disposable data as of the current date time.
```