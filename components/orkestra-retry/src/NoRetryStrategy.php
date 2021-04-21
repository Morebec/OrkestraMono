<?php

namespace Morebec\Orkestra\Retry;

/**
 * Strategy that simply do not orkestra-retry and let exception go through.
 */
class NoRetryStrategy implements RetryStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute(callable $fun)
    {
        $fun();
    }
}
