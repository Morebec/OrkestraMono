<?php

namespace Morebec\Orkestra\EventSourcing\Projection;

use Morebec\Orkestra\EventSourcing\EventProcessor\EventPublisherInterface;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;

/**
 * Event publisher that publishes an event to a given {@link ProjectorInterface}.
 */
class ProjectorEventPublisher implements EventPublisherInterface
{
    private ProjectorInterface $projector;

    public function __construct(ProjectorInterface $projector)
    {
        $this->projector = $projector;
    }

    public function publishEvent(RecordedEventDescriptor $eventDescriptor): void
    {
        $this->projector->project($eventDescriptor);
    }

    public function getProjector(): ProjectorInterface
    {
        return $this->projector;
    }
}
