<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\SubscriptionOptions;

/**
 * Wraps {@link EventStoreSubscriberInterface}.
 *
 * @internal
 */
class PostgreSqlSubscriberWrapper implements EventStoreSubscriberInterface
{
    /**
     * @var EventStoreSubscriberInterface
     */
    private $subscriber;

    public function __construct(EventStoreSubscriberInterface $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function onEvent(EventStoreInterface $eventStore, RecordedEventDescriptor $eventDescriptor): void
    {
        $this->subscriber->onEvent($eventStore, $eventDescriptor);
    }

    public function getOptions(): SubscriptionOptions
    {
        return $this->subscriber->getOptions();
    }
}
