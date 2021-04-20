<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;

/**
 * An Event publisher is responsible for sending events out the event store to other components of the system
 * These components could be:
 * - Event handlers and process managers for side-effects
 * - Projectors for Read Models
 * - Notification Systems for Administration
 * - etc.
 */
interface EventPublisherInterface
{
    /**
     * Publishes an event.
     */
    public function publishEvent(RecordedEventDescriptor $eventDescriptor): void;
}
