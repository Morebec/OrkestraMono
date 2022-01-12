<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Precondition;

/**
 * Precondition being a composite of other preconditions.
 */
class CompositePrecondition implements TestStagePreconditionInterface
{
    /** @var TestStagePreconditionInterface[] */
    private array $preconditions;

    /**
     * @param TestStagePreconditionInterface[] $preconditions
     */
    public function __construct(array $preconditions = [])
    {
        $this->preconditions = [];
        foreach ($preconditions as $precondition) {
            $this->addPrecondition($precondition);
        }
    }

    public function addPrecondition(TestStagePreconditionInterface $precondition): self
    {
        $this->preconditions[] = $precondition;

        return $this;
    }

    public function run(): void
    {
        foreach ($this->preconditions as $precondition) {
            $precondition->run();
        }
    }
}
