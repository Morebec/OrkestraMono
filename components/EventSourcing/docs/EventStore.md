# Event Store
*This document explains how to use the Event Store of this component.*

The Event Store append only data storage used to store events which are immutable data structure describing facts. 
(E.g. `User Registered` or `Product Pruchased`). By using events it allows to perform a number of interesting
things from notifying remote systems, building historical reports, to triggering side effects in a system.

For a conceptual point of view it serves to store the state changes of objects instead of simply their current state in a database.
You can think of it as allowing to version your objects like you would use git for project files.

Events are organised into streams which correspond to a historical grouping of related events such as all the events
that happened to the account of a given user for instance.

The Orkestra Event Store component provides an interface, the `EventStoreInterface` that serves to abstract working with
an implementation of an `EventStore`. In the Orkestra Components, an official `PostgreSQL`implementation is provided.

## Append to a Stream
Events are organized into streams. These streams represent groupings of related events.
In order to append events to a stream, you must use the `EventStoreInterface::appendStream` method that takes
the `EventStreamId`, a list of `EventDescriptors` and an `AppendStreamOptions`.

Events are represented by the `EventDescriptorInterface` which is used as a wrapper around event data
so that the event store can effectively persist these events.

They are comprised of:
- an `EventId` which corresponds to the unique ID of the event.
- an `EventType` which corresponds to the type of the event. (e.g. `user.registered`)
- an `EventData` which is a map like data structure to represent the data of the event.
- an `EventMetadata` which is a map like data structure that represents metadata about the event.

You can use the default implementation `EventDescriptor` when appending to a stream or the `EventDescriptorBuilder`
which provides a fluent APi for building event descriptors.


```php
$eventStore->appendStream(EventStreamId::fromString(), [$event], new AppendStreamOptions());
```

### Optimistic Concurrency Control
It is possible when appending to a stream to ensure that no other process has added conflicting changes 
to the stream. This is performed with Optimistic Concurrency Control by relying on the Event Stream Version.
Every time an event is added to a stream, its Stream Version gets incremented. 
This can be used to specify which version a stream is expected to be at, in order to safely append new events.

```php
AppendStreamOptions::append()
    ->expectedStreamVersion(EventStreamVersion::fromInt(120))
    
    // To disable this check (default)
    ->disableOptimisticConcurrencyCheck
;
```
## Read from a Stream
In order to read from a stream, you must use the `EventStoreInterface::readStream` method which takes
two arguments: one for the stream Identifier and another for the reading options:
```php
$streamId = EventStreamId::fromString('a-stream');
$events = $eventStore->readStream($streamId, ReadStreamOptions::read()->forward()->fromStart());
```
> Note: If a stream does not exist, an exception is thrown.

The returned result is a `StreamedEventCollectionInterface` that contains a list of the events read.
It provides utility methods to easily filter these events to perform your own logic on them. It also implements
`\Iterator` which means that it can be iterated using `foreach` loops.

The events contained in this collection are of type `RecordedEventDescriptor` which are an implementation of the `EventDescriptorInterface`
that provides additional information about the event such as the date time at which the event was appended to the event store,
to which stream, at which version and finally at which sequence number.


The reading options allow to change the way a stream is read:

```php
$options = ReadStreamOptions::read()
    // Read in a specific direction
    ->forward() // default
    ->backward()
    
    // From a specific location
    ->fromStart() // default
    ->fromEnd()
    
    // This position can be a stream version number or a sequence number depending
    // if you are reading a specific stream or the global stream respectively.
    ->from($position)
    // Allows to limit the number of results if necessary. This can allow to read in batches.
    // defaults to 1000 
    ->maxCount(1000)
;
```
> Note: When reading from a given position (other than Start or End), the event corresponding to this exact 
> position will not be including in the result set.

It also provides utility methods to easily read the event stream in specific ways such as:

```php
$options = ReadStreamOptions::lastEvent(); // Will return the last event of a stream.
```


### Read from Global Stream
Reading from the global stream simply requires to feed the global `streamId` to the `readStream` method.

The event store has a method that returns the identifier of that stream:
```php
$globalStreamId = $eventStore->getGlobalStreamId();
$events = $eventStore->readStream($globalStreamId, ReadStreamOptions::read()->forward()->fromStart());
```


## Getting information about a Stream
If you need to get information about a stream you can use the `EventStoreInterface::getStream` method which returns a
`EventStreamInterface` object containing the ID of the stream and its current version (that can also serve as the number of events in the stream).

### Finding out if a stream exists
To find out if a stream exists, you can use the `EventStoreInterface::streamExists` method.