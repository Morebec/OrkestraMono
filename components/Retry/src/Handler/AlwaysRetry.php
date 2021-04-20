<?php

namespace Morebec\Orkestra\Retry\Handler;

use Morebec\Orkestra\Retry\RetryContext;

/**
 * Handler that always retries or until the max attempt has been reached.
 * To use with {@link RetryStrategy}.
 */
class AlwaysRetry
{
    public function __invoke(RetryContext $context, \Throwable $throwable): bool
    {
        return true;
    }
}
