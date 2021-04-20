## Event Processors

Events coming out of an application model can be used as, an integration model for other systems, 
to trigger side effects on the same system from which these events originated, or compute read models
from these events.

They can be dispatched synchronously or to a queue in an asynchronous manner.

The `Event Processors` are services responsible for handling events out of the event store
and delegate them to the required components of the system.

In essence, the Event Processors represented by the `EventProcessorInteface` are responsible for handling the technical side of processing events once
they come out of the event store: Whether this happens in real time (subscriptions), or by moving up the history of events (tracking) is
up to the processor implementation to determine.


Orkestra provides implementations for three types of Event Processors:
- `SubscribedEventProcessor` Gets notified of new events in the `EventStore` in realtime and delegate the actual work to an `EventPublisherInterface`.
- `TrackingEventProcessor` Tracks a given stream of the `EventStore` for new events and tries to make progress in that stream until it reaches the end delegating the work to an `EventPublisherInterface`.
- `SubscribedTrackingEventProcessor` Both Tracks a given stream of the `EventStore` and subscribes to it to trigger tracking every time an event is received.

These three types of processor can handle most use cases of event sourced systems.

Which one to use, is highly dependent on your requirements.

Let's see a few examples.

### Dispatching on the message bus for side effects
In order for the different event handlers registered with the message bus to be triggered when certain event occurs, we need
a way to send these messages from the event store to the bus. 

This component has a few requirements:
- A. We want messages to be sent to the bus as soon as possible to maintain low SLAs.
- B. In the event that the component of the application that is supposed to do this work would be down, we don't want it to lose events. 
  We want it to be able to process the missed events as well as the new incoming events.

For a requirement A, we have two options:
- Subscribing to the event store to have notifications in realtime of new events.
- Continuously polling the event store for new events

The `SubscribedEventProcessor` has this exact purpose and can be notified by the event store as soon as new events are appended to a stream. However, 
it does not keep progress of where it is located in the event store. This means that if this processor becomes offline for any reason, it won't be able to process 
the event it missed during its time offline. 
This would give us requirement A, but not requirement B.

Continuously polling the event store is a task the the `TrackingEventProcessor` is capable of doing out of the box, however it *can* pose performance issues depending on the implementation of the event store
used as well as the available resources of the system. To mitigate this it allows to specify a delay between calls to the event store for new events. Take note that, this polling delay would raise our SLAs a bit, and still consume unecessary network calls to the Event Store even if there are no new events for some amount of time.

So this option would give us what we need for a requirement B, but would not be as instant for requirement A. AS said, though, depending on the event store used, it can still be a viable solution.

One thing we could do is to prevent the `TrackingEventProcessor` from polling the event store again once it has completed its work, and only start it again when new events come in.
The `TrackingEventProcessor` can be configured to only run once every time it is started. In fact this is the default configuration it uses. 
A simple implementation would require creating an `EventStoreSubscriberInterface` that would start this processor whenever it receives a new event.


Essentially, this last option would correspond to a mix of the two processors: The `SubscribedTrackingEventProcessor`, which works a Tracking Behaviour that only runs once, and gets started again anytime new events come in form the event store.


### Processing events concurrently
Sometimes for performance reasons you might need to process events concurrently. 
For example, you could have a system where a a `ProductPurchasedEvent` is fired where multiple `DomainEventHandlers` are registered take actions on it:
- Notify Stock Management System
- Notify Administrators of a new purchase by instant messaging
- Generate receipt for purchase
- Notify Shipping service to prepare a shipment

All these actions could be performed concurrently. If all your event handlers are registered with the same message bus, and you use a single event processor,
these handlers would be processed independently, but sequentially. In cases where these actions take time, say, 1min each to process, it would take 4 minutes to process this event.
On the other hand, if you had 4 event processors, processing this event would only take around 1 minute as they could be run concurrently.

The challenge with this is making sure that each processor end up dispatching the event to a single specific handler.

```php
$postgreSqlProjector = new PostgresqlProjector();
$postgreSqlProjector->boot();

$publisher = new ProjectorEventPublisher($posgreSqlProjector);

$options = SubscribedTrackingEventProcessorOptions::make()
	->withName(PostgresqlProjector::getTypeName())
	->withStreamId()
;

$processor = new SubscribedTrackingEventProcessor($publisher, $options);

$processor->start();

$posgreSqlProjector->shutdown();
```