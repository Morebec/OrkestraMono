# Projection
A Projection is a specific representation of Events into data structures. 
These data structures are also known as Read Models, or view.

## Projectors
A Projector is the computational unit responsible for transforming Events to Projections.
They implement the interface `ProjectorInterface`. They have multiple methods
to handle booting them up, shutting them down and replaying events to rebuild the projections.
The most important method of the Projector is the  `ProjectorInterface::project(RecordedEventDescroptor)`.

```php
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\Projection\ProjectorInterface;

class UserProjector implements ProjectorInterface
{
    public function boot() : void
    {
        // Connect to SQL, MongoDb database etc.
    }
    
    public function project(RecordedEventDescriptor $descriptor) : void
    {
        $data = $descriptor->getEventData();
        if ($descriptor->getEventType()->isEqualTo(EventType::fromString(UserRegisteredEvent::getTypeName()))) {
            $this->database->insert(
                new User(
                    $data['id'],
                    $data['username'],
                    $data['fullname'],
                    $data['emailAddress']
                )
            );
        
            // Alternatively you could denormalize the data into a typed object and work with this instead.
            /** @var DomainMessageNormalizerInterface $normalizer */
            $event = $normalizer->denormalize($descriptor->getEventData(), UserRegisteredEvent::class);
            
            $this->database->insert(
                new User(
                    $event->id,
                    $event->username,
                    $event->fullname,
                    $event->emailAddress
                )
            );
        }
       
    }
    
    public function shutdown() : void
    {
        // Close connection
    }
    
    public function reset() : void
    {
        // Drop tables
    }
    public static function getTypeName() : string
    {
        return 'users';
    }
}
```

This is relatively simple and straightforward, but can be cumbersome to type.
To help with this, the Event Sourcing component provides abstract implementations that does some repetitive tasks
for you.

### AbstractTypedEventProjector
This implementation automatically handles denormalizing `RecordedEventDescriptors` into
their concrete type as well as calling the right handler methods.
```php
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\Projection\AbstractTypedEventProjector;
use Morebec\Orkestra\EventSourcing\Projection\ProjectorInterface;

class UserProjector extends AbstractTypedEventProjector
{
    public function boot() : void
    {
        // Connect to SQL, MongoDb database etc.
    }
   
   public function onUserRegistered(UserCreated $event, RecordedEventDescriptor $descriptor /* this parameter is optional see below */): void 
   {
       $this->database->insert(
            new User(
                $event->id,
                $event->username,
                $event->fullname,
                $event->emailAddress
            )
       );
   }
   
   // The descriptor can also be provided if you add a second argument to the method.
   public function onUserEmailAddressChanged(UserEmailAddressChangedEvent $event, RecordedEventDescriptor $descriptor): void
   {
        $user = $this->database->find($event->id);
        $user->emailAddress= $event->emailAddress;
        $this->database->update($user);
   }
    
    public function shutdown() : void
    {
        // Close connection
    }
    
    public function reset() : void
    {
        // Drop tables
    }
    public static function getTypeName() : string
    {
        return 'users';
    }
}
```
It routes the events to the right methods by using the convention of a method starting with `on` and having an
argument typehinted with the event type the method can handle. A second argument can also be provided by typehinting
the RecordedEventDescriptor class.

## Dispatching Events to Projectors
The way to dispatch events to projectors is through the `EventProcessorInterface`. 
It is advised to use a `SubscribedTrackingEventProcessor` combined with a `ProjectorEventPublisher`:

```php
$postgreSqlProjector = new PostgresqlProjector();
$postgreSqlProjector->boot();

$publisher = new ProjectorEventPublisher($postgreSqlProjector);

$options = SubscribedTrackingEventProcessorOptions::make()
	->withName(PostgresqlProjector::getTypeName())
	->withStreamId()
;

$processor = new SubscribedTrackingEventProcessor($publisher, $options);

$postgreSqlProjector->boot();
$processor->start();


$posgreSqlProjector->shutdown();
```

### Projection Groups
Projection groups represented by the `ProjectorGroup` class allows grouping multiple projectors together in a single cohesive unit, so they can all be operated 
as if they were one. This is useful in cases where some projector needs to perform lookups to projections
of other projectors in order to do its work.
