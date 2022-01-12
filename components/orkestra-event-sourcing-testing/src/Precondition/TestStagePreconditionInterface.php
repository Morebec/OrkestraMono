<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Precondition;

interface TestStagePreconditionInterface
{
    /**
     * Runs this precondition.
     */
    public function run(): void;
}
