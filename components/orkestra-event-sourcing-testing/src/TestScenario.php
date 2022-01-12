<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\UnsatisfiedExpectationException;
use Throwable;

class TestScenario
{
    /** @var TestStage[] */
    private array $stages;

    private EventStoreInterface $eventStore;

    /**
     * @param TestStage[] $stages
     */
    public function __construct(EventStoreInterface $eventStore, array $stages = [])
    {
        $this->stages = [];
        foreach ($stages as $stage) {
            $this->addStage($stage);
        }

        $this->eventStore = $eventStore;
    }

    /**
     * Adds a stage to this scenario.
     *
     * @return $this
     */
    public function addStage(TestStage $stage): self
    {
        $this->stages[] = $stage;

        return $this;
    }

    /**
     * Runs this scenario.
     *
     * @throws Throwable
     * @throws UnsatisfiedExpectationException
     */
    public function run(): void
    {
        foreach ($this->stages as $stage) {
            $stage->run();
        }
    }

    public function getEventStore(): EventStoreInterface
    {
        return $this->eventStore;
    }
}
