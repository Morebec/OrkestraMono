<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreSubscriberInterface;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\EventStore\SubscriptionOptions;

/**
 * This processor subscribes to the event store to track every time a new event is available
 * for processing and forward it to a given dispatcher.
 * Essentially it allows processing events in realtime.
 * It needs to be registered with the {@link EventStoreInterface}.
 *
 * It can be useful for example to provide an API that sends change streams to clients
 * such as Web Apps.
 */
class SubscribedEventProcessor implements EventProcessorInterface, EventStoreSubscriberInterface
{
    /**
     * @var EventPublisherInterface
     */
    private $eventPublisher;

    /**
     * @var string
     */
    private $name;

    /** @var bool */
    private $running;

    public function __construct(string $name, EventPublisherInterface $eventPublisher)
    {
        $this->eventPublisher = $eventPublisher;
        $this->name = $name;
        $this->running = false;
    }

    public function onEvent(EventStoreInterface $eventStore, RecordedEventDescriptor $eventDescriptor): void
    {
        if ($this->running) {
            $this->eventPublisher->publishEvent($eventDescriptor);
        }
    }

    public function getOptions(): SubscriptionOptions
    {
        return SubscriptionOptions::subscribe()->fromEnd();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function start(): void
    {
        $this->running = true;
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}
