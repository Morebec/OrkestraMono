<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\EventSourcing\EventStore\AppendStreamOptions;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptorInterface;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\CompositePrecondition;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\DomainEventRecordedInEventStorePrecondition;
use Morebec\Orkestra\EventSourcing\Testing\Precondition\EventDescriptorInEventStorePrecondition;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;

class TestStageUsingStreamBuilder extends TestStageBuilder
{
    private EventStreamId $streamId;

    public function __construct(
        TestScenarioBuilder $scenarioBuilder,
        TestStage $stage,
        EventStreamId $streamId
    ) {
        parent::__construct($scenarioBuilder, $stage);
        $this->streamId = $streamId;
    }

    /**
     * Allows to specify that at the start of the stage a given event was recorded in the event store,
     * as a precondition.
     *
     * @param DomainEventInterface|EventDescriptorInterface $event
     */
    public function recorded($event, ?AppendStreamOptions $options = null): self
    {
        $precondition = $event instanceof DomainEventInterface ? new DomainEventRecordedInEventStorePrecondition(
            $this->scenarioBuilder->getMessageBus(),
            $this->scenarioBuilder->getMessageNormalizer(),
            $this->scenarioBuilder->getEventStore(),
            $event,
            $this->streamId,
            $options
        ) : new EventDescriptorInEventStorePrecondition(
            $this->scenarioBuilder->getEventStore(),
            $this->scenarioBuilder->getMessageBus(),
            $this->scenarioBuilder->getMessageNormalizer(),
            $event,
            $this->streamId,
            $options
        );

        /** @var CompositePrecondition $composite */
        $composite = $this->stage->getPrecondition();
        $composite->addPrecondition($precondition);

        return $this;
    }

    /**
     * Allows to specify that at the start of the stage a given event was recorded in the event store,
     * as a precondition.
     *
     * @param DomainEventInterface|EventDescriptorInterface $event
     */
    public function andRecorded($event, ?AppendStreamOptions $options = null): self
    {
        return $this->recorded($event, $options);
    }
}
