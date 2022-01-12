<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Precondition;

class NoopPrecondition implements TestStagePreconditionInterface
{
    /**
     * {@inheritDoc}
     */
    public function run(): void
    {
    }
}
