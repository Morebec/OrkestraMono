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

Orkestra provides implementations for four types of Event Processors:
- `SubscribedEventProcessor` Gets notified of new events in the `EventStore` in realtime and delegate the actual work to an `EventPublisherInterface`.
- `TrackingEventProcessor` Tracks a given stream of the `EventStore` for new events (since its last work) and tries to make progress in that stream until it reaches the end delegating the work to an `EventPublisherInterface`.
- `SubscribedTrackingEventProcessor` Both Tracks a given stream of the `EventStore` and subscribes to it to trigger tracking every time an event is received.
- `PollingTrackingEventProcessor` Continuously polls the event store for new events. Once it has processed a batch of events, it polls the database for new events in an infinite loop or until it has been called to stop.

These four types of processor can handle most use cases of event sourced systems.

Which one to use, is highly dependent on your requirements.

Let's see a few examples.

### Dispatching on the message bus for side effects
In order for the different event handlers registered with the message bus to be triggered when certain events occur, we need
a way to send these messages from the event store to the message bus. 

This component has a few requirements:
- A. We want messages to be sent to the bus as soon as possible to maintain low SLAs.
- B. In the event that the component of the application that is supposed to do this work would be down, we don't want it to lose events. 
  We want it to be able to process the missed events as well as the new incoming events.

For a requirement A, we have two options:
1. Subscribing to the event store to have notifications in realtime of new events.
2. Continuously polling the event store for new events

The `SubscribedEventProcessor` has the exact purpose of option 1.: it is notified by the event store as soon as new events are appended to a stream. However, 
it does not keep progress of where it is located in the event store. This means that if this processor becomes offline for any reason, it won't be able to process 
the event it missed during its time offline. 
This would give us requirement A, but not requirement B.

Continuously polling the event store is a task the the `PollingTrackingEventProcessor` is capable of doing out of the box, however it *can* pose performance issues depending on the implementation of the event store
used as well as the available resources of the system. To mitigate this it allows to specify a delay between calls to the event store for new events. Take note that, this polling delay would raise our SLAs a bit, 
and still consume unnecessary network calls to the Event Store when there are no new events for some amount of time.

So this option would give us what we need for a requirement B, but would not be as instant for requirement A. 
However, as previously said, depending on the event store used, it can still be a viable solution.

One thing we could do is to benefit from both the `TrackingEventProcessor` and the `SubscribedEventProcessor` implementations
and only track events whenever new ones are appended to the stream, while still being able to catchup if we have missing ones.
This is precisely the job of the `SubscribedTrackingEventProcessor`.

### Processing events concurrently
Sometimes for performance reasons you might need to process events concurrently. 
For example, you could have a system where a a `ProductPurchasedEvent` is fired where multiple `DomainEventHandlers` are registered to take actions on it:
- Notify Stock Management System
- Notify Administrators of a new purchase by instant messaging
- Generate receipt for purchase
- Notify Shipping service to prepare a shipment

All these actions could be performed concurrently. If all your event handlers are registered with the same message bus, and you use a single event processor,
these handlers would be processed independently, but sequentially. In cases where these actions take time, say, 1min each to process, it would take 4 minutes to process this event.
On the other hand, if you had 4 event processors, processing this event would only take around 1 minute as they could be run concurrently.

The challenge with this is making sure that each processor end up dispatching the event to a single specific handler.

To achieve this you have multiple strategies available:
1. Bypass the MessageBus entirely and directly pass the events to the interested handlers.
2. Prior to publishing the events on the message bus, pre-route the event messages to the right handlers using he `MessageHeaders::DESTINATION_HANDLER_NAMES`.
   This strategy
3. Have a filtered event processor, and make your event handlers idempotent, so that they can simply skip the events they have already seen.
   
Strategy 1 and 2 can be done by implementing your own `EventPublishers` to be added to the 4 event processors.
Strategy 3 has the benefit of not requiring implementing a custom EventPublisher, but has the drawbacks
that in the logs the event will still be sent by each event processor to each handler at least 4 times,
it introduces *at-least once* delivery guarantees as opposed to *at-most once* to all your event handlers interested
in this event as they will all be required to be idempotent.

Which option is best, again highly depends on your system's requirements. However, before trying to integrate
event processing concurrency, check if it is really required. Maybe other optimization techniques can be performed
before going this way.

## Listening to Event Processors
There can be cases where it is useful to listen to the work of the event processors to perform some tasks
before they are started, after they are stopped or before and after they process an event. Such cases
could be to compile statistics, logging, checking the progress of a processor or even stop the processor
under specific circumstances.

To this goal, the builtin Event Processors all implement the `ListenableEventProcessor` which allows
adding `EventProcessorListenerInterface` to them.

## Projections
Another use case for event processing is building projections a.k.a Read Models.
Read the [Projection](./Projection.md) document to learn more.