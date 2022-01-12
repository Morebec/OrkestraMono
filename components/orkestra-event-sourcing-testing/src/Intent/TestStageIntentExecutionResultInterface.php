<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Intent;

use Morebec\Orkestra\EventSourcing\Testing\TestScenario;
use Morebec\Orkestra\EventSourcing\Testing\TestStage;
use Throwable;

/**
 * Represents the result of executing a TestStageIntentExecution.
 */
interface TestStageIntentExecutionResultInterface
{
    /**
     * Returns the scenario as part of which this intent execution occurred.
     */
    public function getScenario(): TestScenario;

    /**
     * Returns the stage as part of which this intent execution occurred.
     */
    public function getStage(): TestStage;

    /**
     * Indicates if the execution was a failure.
     */
    public function isFailure(): bool;

    /**
     * Indicates if the execution was a success.
     */
    public function isSuccess(): bool;

    /**
     * Returns a throwable if the execution encountered any.
     *
     * @return ?Throwable
     */
    public function getThrowable(): ?Throwable;

    /**
     * Indicates if a throwable was encountered during the execution.
     */
    public function hasThrowable(): bool;

    /**
     * Returns the payload returned by the execution.
     * If the execution encountered an exception, this returns the exception.
     *
     * @return mixed|Throwable
     */
    public function getPayload();
}
