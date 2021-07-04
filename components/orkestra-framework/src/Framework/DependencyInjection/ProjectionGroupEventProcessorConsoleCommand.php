<?php

namespace Morebec\Orkestra\OrkestraFramework\Framework\DependencyInjection;

use Morebec\Orkestra\EventSourcing\EventProcessor\EventProcessorInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventStorePositionStorageInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\TrackingEventProcessor;
use Morebec\Orkestra\EventSourcing\EventProcessor\TrackingEventProcessorOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\Projection\ProjectorEventPublisher;
use Morebec\Orkestra\SymfonyBundle\Command\AbstractProjectionGroupEventProcessorConsoleCommand;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\ProjectorGroupRegistry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectionGroupEventProcessorConsoleCommand extends AbstractProjectionGroupEventProcessorConsoleCommand
{
    public const DEFAULT_BATCH_SIZE = 1000;

    /** @var EventStoreInterface */
    private $eventStore;

    /** @var EventStorePositionStorageInterface */
    private $eventStorePositionStorage;

    public function __construct(
        EventStoreInterface $eventStore,
        ProjectorGroupRegistry $projectorGroupRegistry,
        EventStorePositionStorageInterface $eventStorePositionStorage
    ) {
        $this->eventStore = $eventStore;
        $this->eventStorePositionStorage = $eventStorePositionStorage;
        parent::__construct($projectorGroupRegistry, 'orkestra:projection-processor');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'batchSize',
            null,
            InputOption::VALUE_OPTIONAL,
            'Batch size when reading events.',
            self::DEFAULT_BATCH_SIZE
        );
    }

    protected function getProcessor(InputInterface $input, OutputInterface $output): EventProcessorInterface
    {
        $projectorGroup = $this->getProjectorGroup($input, $output);

        return new TrackingEventProcessor(
            new ProjectorEventPublisher($projectorGroup),
            $this->eventStore,
            $this->eventStorePositionStorage,
            (new TrackingEventProcessorOptions)
                ->withName($projectorGroup->getName())
                ->storePositionAfterProcessing()
                ->withBatchSize((int)$input->getOption('batchSize'))
                ->storePositionPerEvent(true)
                ->withStreamId($this->eventStore->getGlobalStreamId())
        );
    }
}