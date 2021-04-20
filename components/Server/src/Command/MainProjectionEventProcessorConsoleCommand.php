<?php

namespace Morebec\Orkestra\OrkestraServer\Command;

use Morebec\Orkestra\EventSourcing\EventProcessor\EventStorePositionStorageInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\Projection\ProjectorEventPublisher;
use Morebec\Orkestra\OrkestraServer\Api\v1\PostgreSqlProjectorGroup;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventProcessor;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStore;
use Morebec\OrkestraSymfonyBundle\Command\AbstractEventProcessorConsoleCommand;

// TODO Make generic.
class MainProjectionEventProcessorConsoleCommand extends AbstractEventProcessorConsoleCommand
{
    protected static $defaultName = 'orkestra:projection-processor';
    /**
     * @var PostgreSqlProjectorGroup
     */
    private $projector;

    public function __construct(
        PostgreSqlProjectorGroup $projector,
        EventStoreInterface $eventStore,
        PostgreSqlEventStore $postgreSqlEventStore,
        EventStorePositionStorageInterface $eventStorePositionStorage
    ) {
        $processor = new PostgreSqlEventProcessor(
            new ProjectorEventPublisher($projector),
            $eventStore,
            $postgreSqlEventStore,
            $eventStorePositionStorage
        );

        $this->projector = $projector;
        parent::__construct($processor, null, 'PostgreSQL Projection Processor');
    }

    protected function resetProcessor(): void
    {
        $this->projector->reset();
        parent::resetProcessor();
    }
}
