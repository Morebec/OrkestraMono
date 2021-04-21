# Upcasting
*This document explains the process of upcasting events, and the tools offered by the component to tackle this challenge*.

The process of upcasting corresponds to applying transformations to events so that they can comply to specific new schemas.

When storing events to an Event store, the data and the structure it has, must live indefinitely. Indeed, an event store is an append only
data storage and, therefore, it cannot delete events or update them.

As with a lot of software requirements can change and event schemas can evolve. This can pause a problem where old events no longer have the right schema to 
be denormalized to typed event.

In more traditional architectures where the state of object is saved, the most common strategy is usually to run migrations over the database.

One solution that is used with event sourcing is upcasting, which essentially means transforming events from one schema version to the other
when reading from the Event Store.

The Event Sourcing component provides some tools to perform this task.

## Upcasters
Upcasters represented by the `UpcasterInterface` are responsible for transforming events from one schema to another.

They take as input an instance of `UpcastableEventDescriptor` which are mutable implementation of `RecordedEventDescriptors`.

Out of the box the Event Sourcing component provides a few Abstract implementations:
- `AbstractEventSpecificUpcaster` which allows to easily implement upcasters that are interested in a specific type
of Event.
- `AbstractEventSpecificUpcaster` extends the `AbstractEventSpecificUpcaster` and allows implementing upcasters only supporting events
of a specific version (with metadata key 'schemaVersion').
- `AbstractSingleEventUpcaster` extends `AbstractEventSpecificUpcaster` allows defining in a friendlier api upcasters to convert an event of a given type to a single event of a new schema.
- `AbstractMultiEventUpcaster`  extends `AbstractEventSpecificUpcaster` allows defining in a friendlier api upcasters to convert an event of a given type to multiple events (demultiplexing).
- `UpcasterChain` allows to pass an event descriptor through a series of upcasters. It works like a pipe passing the result of the previous upcaster to the next one until a final result is obtained.

Let's review a few examples with common operations required when upcasting.

## Skipping
Sometimes, events can become obsolete, that is, an event that was once used by the application is no longer of interest to it, because it has been implemented differently
or because it has changed completely. In these cases what we essentially want is to skip this event and never return it.

Here's how we can implement a skipping upcaster.

```php

class SkippingUpcaster extends AbstractEventSpecificUpcaster {
    public function __construct() {
        parent::__construct(MyEvent::getTypeName());
    }
    public function upcast(UpcastableEventDescriptor $event) : array {
        return []; // Skipping
    }
}
```

## Demultiplexing
There can be cases where we want to demultiplex events. This essentially means taking an event and splitting it into two events.
For example, we might have an event like `FullnameChanged` that was split into two different events `FirstnameChanged` and `LastnameChanged`.
This is taking a single event and upcasting it into multiple events.

```php
class FullnameChangedDemultiplexer extends AbstractMultiEventUpcaster {
    public function __construct() {
        parent::__construct(MyEvent::getTypeName());
    }
    
    public function doUpcast(UpcastableEventDescriptor $descriptor) {
        $fullname = $descriptor->getField('fullname');
        [$firstName, $lastName] = explode(' ', $fullname);
        
        $firstNameChanged = $descriptor->withType('user.first_name_changed')->withData(['firstName' => $firstName]); 
        $lastNameChanged = $descriptor->withType('user.last_name_changed')->withData(['firstName' => $lastName]);
        
        return [$firstNameChanged, $lastNameChanged]; 
    }
}
```

### Adding a new field
Adding a field to an event is one of the most common change that we people perform.
Here's how you can do it.
```php
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractSingleEventUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;

class AddingAField extends AbstractSingleEventUpcaster {
    public function __construct() 
    {
        parent::__construct(MyEvent::getTypeName());
    }
    protected function doUpcast(UpcastableEventDescriptor $eventDescriptor) : UpcastableEventDescriptor
    {
        // Or the shorter version
        return $eventDescriptor->withFieldAdded('middleName', $yourDefaultValue);
    }
}
```
### Renaming a field
Renaming a field is an operation that often happens. Here's how we can do this in an upcaster.
```php
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractSingleEventUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;

class RenamingAField extends AbstractSingleEventUpcaster {
    public function __construct() 
    {
        parent::__construct(MyEvent::getTypeName());
    }
    protected function doUpcast(UpcastableEventDescriptor $eventDescriptor) : UpcastableEventDescriptor
    {
        return $eventDescriptor->withFieldRenamed('lastName', 'surname');
    }
}
```

### Removing a field
Removing a field is also very simple using an upcaster:
```php
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractSingleEventUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;

class RenamingAField extends AbstractSingleEventUpcaster {
    public function __construct() 
    {
        parent::__construct(MyEvent::getTypeName());
    }
    protected function doUpcast(UpcastableEventDescriptor $eventDescriptor) : UpcastableEventDescriptor
    {
        return $eventDescriptor->withFieldRemoved('lastName');
    }
}
```


### Combining changes
Usually however you might need to change multiple things at once, adding, renaming and deleting.
The `UpcastableEventDescriptor::withField*` can all be chained. There are other methods
for handling the metadata `UpcastableEventDescriptor::withMetadata*`. This API on the `UpcastableEventDescriptor`
combined with the concept of Upcasters, make evolving your events, easy and expressive.

```php
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractSingleEventUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;

class MultipleOperations extends AbstractSingleEventUpcaster {
    public function __construct() 
    {
        parent::__construct(MyEvent::getTypeName());
    }
    
    protected function doUpcast(UpcastableEventDescriptor $eventDescriptor) : UpcastableEventDescriptor
    {
        $firstName = $eventDescriptor->getField('firstName');
        $lastName = $eventDescriptor->getField('lastName');
        $middleName = $eventDescriptor->getField('middleName');
        
        return $eventDescriptor
            ->withFieldUpdated('someField', 'a new value')
            ->withFieldRenamed('sex', 'gender')
            ->withFieldRemoved('firstName')
            ->withFieldRemoved('lastName')
            ->withFieldRemoved('middleName')
            ->withFieldAdded('fullname', "$firstName $middleName $lastName")
            ;
    }
}
```

### Adding Upcasting to the Event Store
The `UpcastingEventStoreDecorator` is an implementation of the Decorator pattern over the `EventStoreInterface`.
Basically it can wrap the event store to give it the ability to upcast events, regardless of its concrete implementation:

```php
$postgresSqlEventStore = new PostgesSqlEventStore(/*...*/);
$upcasterChain = UpcasterChain(/***/);
$eventStore = new UpcastingEventStoreDecorator($postgresSqlEventStore, $upcasterChain);
```
