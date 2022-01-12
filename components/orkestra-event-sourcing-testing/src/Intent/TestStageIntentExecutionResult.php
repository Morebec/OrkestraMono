<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Intent;

use Morebec\Orkestra\EventSourcing\Testing\TestScenario;
use Morebec\Orkestra\EventSourcing\Testing\TestStage;
use Throwable;

class TestStageIntentExecutionResult implements TestStageIntentExecutionResultInterface
{
    private bool $succeeded;

    private $payload;

    private TestStage $stage;

    public function __construct(TestStage $stage, bool $succeeded, $payload)
    {
        $this->succeeded = $succeeded;
        $this->payload = $payload;
        $this->stage = $stage;
    }

    /**
     * {@inheritDoc}
     */
    public function isFailure(): bool
    {
        return !$this->succeeded;
    }

    /**
     * {@inheritDoc}
     */
    public function isSuccess(): bool
    {
        return $this->succeeded;
    }

    /**
     * {@inheritDoc}
     */
    public function getThrowable(): ?Throwable
    {
        return $this->payload instanceof Throwable ? $this->payload : null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasThrowable(): bool
    {
        return $this->payload instanceof Throwable;
    }

    /**
     * {@inheritDoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function getScenario(): TestScenario
    {
        return $this->stage->getScenario();
    }

    public function getStage(): TestStage
    {
        return $this->stage;
    }
}
