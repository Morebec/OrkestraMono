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


Here's a more complete example:

```php
use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptorBuilder;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;

$streamId = EventStreamId::fromString('your-stream-id');

// The options class is used in order to alter the behaviour of the event store when appending the events to the stream
$options = AppendStreamOptions::append()
    // For optimistic concurency check
    ->expectVersion(EventStreamVersion::fromInt(50))
;

// If you want don't want to perform an optimistic concurrency check, simply disable it:
$options->disableOptimisticConcurrencyCheck();

// Events are appended using event recorders.
$events = [
    EventDescriptorBuilder::create()
        ->withId(uniqid('evt_', true))
        ->withType('user.registered')
        ->withData([
            'username' => 'barney.stinson',
            'emailAddress' => 'barney@email.com'
        ])
        ->withMetadata([
            'correlationId' => '1247djjoiUxPzlj',
            'causationId' => '578djjUiZpow=',
            'tenantId' => '778doOPwzgs'
        ])
        ->build()
];

/** @var EventStoreInterface $eventStore */
$eventStore->appendToStream($streamId, $events, $options);
```

> The `EventRecorderInterface`is used to represent an event form the event store point of view. It actually serves as a wrapper around an event's data, and it supports having a unique identifier, a type as well as metadata.
The component provides a default implementation for events that are intended to be added to the event store. Another implementation
is also provided for events that were actually recorded to the event store and another one for events that needs to be upcasted.

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
    
    // You can also filter the event types to read:
    ->filterEventTypes([
        EventType::fromString('user_account.registered'),
        EventType::fromString('user_account.closed'),
    ])
    
    // If you want only ignore a few event types you can use the ignoreEventTypes function:
    ->ignoreEventTypes([
        EventType::fromString('user_account.email_address_changed'),
        EventType::fromString('user_account.fullname_changed'),
    ])
;
```
> Note: When reading from a given position (other than Start or End), the event corresponding to this exact 
> position will not be including in the result set.

It also provides utility methods to easily read the event stream in specific ways such as:

```php
$options = ReadStreamOptions::lastEvent(); // Will return the last event of a stream.
$options = ReadStreamOptions::firstEvent(); // Will return the last event of a stream.
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

## Subscribing
As part of its contract The `EventStoreInterface` has the concept of Subscribers which can be used to tail the event store
for new events as they are appended.

To subscribe to the event store, you can use the `EventStoreInterface::subscribeToStream` method which takes as arguments
the Identifier of the stream to subscribe to and a `EventStoreSubscriberInterface` instance.

The `EventStoreSubscriberInterface` is an interface used to define subscribers to the event store, it has two methods:
`EventStoreSubscriberInterface::onEvent` that is called whenever an event should be notified to this subscriber,
and the `EventStoreSubscriberInterface::getOptions` which returns a `SubscriptionOptions` object that
indicates how the subscriber should be subscribed to the event store.

These options can be used for example to specify if a read of the stream should be performed prior to actually subscribing
in order to allow the subscription to "catch up" before listening to live events.

Here's a simple implementation of an EventStoreSubscriber that simply logs whenever an event gets added:

```php
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\SubscriptionOptions;

class Subscriber implements EventStoreSubscriberInterface
{
    /** @var SubscriptionOptions */
    private $options;
    
    /** @var LoggerInterface */
    private $logger;
    
    public function __construct(LoggerInterface $logger) {
        $this->options = SubscriptionOptions::subscribe()
            // default
            ->fromEnd()
        ;
        
        $this->logger = $logger;
    }
    public function onEvent(EventStoreInterface $eventStore, RecordedEventDescriptor $eventDescriptor) : void
    {
        $this->logger->info(
            sprintf('[Event Store] Event of type "%s" was added to stream "%s" at version "%s" with sequence number "%s".',
                $eventDescriptor->getEventType(),
                $eventDescriptor->getStreamId(),
                $eventDescriptor->getStreamVersion(),
                $eventDescriptor->getSequenceNumber()
            )
        );    
    }
    
    public function getOptions() : SubscriptionOptions
    {
        return $this->options;
    }
}
```

## Decorators
In order to augment the behaviour of the Event Store with disregard to the underlying implementation, the decorator pattern can be used.
This component provides two decorators out of the box.

### MessageBusContextEventStoreDecorate
`MessageBusContextEventStoreDecorate` This decorator adds information from the `MessageBusInterface` of the [Messaging](https://github.com/Morebec/orkestra-messaging) 
as metadata to the event descriptors. It adds the correlation ID, causation ID, application ID, user ID, and tenant ID,
that can be used as contextual information later when processing the events.
If you want to change the contextual information provided from the MessageBus, the decorator can easily be extended
and have its method `processMetadata` overridden.


### UpcastingEventStoreDecorator
This decorator adds to the event store the capability of upcasting events to match new schemas, when reading events
from the store. This can act as a form of in flight migrations.


## Next
The next step after understanding the event store is understanding how to get the events out of the store and
back to the application for side effects and projections.
This is done through [EventProcessing](./EventProcessor.md)
