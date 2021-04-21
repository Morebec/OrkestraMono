<?php

namespace Morebec\Orkestra\Retry;

/**
 * Interface for a orkestra-retry strategy.
 * A orkestra-retry strategy is responsible for describing the conditions under which to orkestra-retry an operation
 * as well as being able to execute an operation following that strategy.
 */
interface RetryStrategyInterface
{
    /**
     * Executes a function and upon failure retries it according to this strategy.
     *
     * @return mixed
     */
    public function execute(callable $fun);
}
