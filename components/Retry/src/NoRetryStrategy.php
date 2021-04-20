<?php

namespace Morebec\Orkestra\Retry;

/**
 * Strategy that simply do not retry and let exception go through.
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
