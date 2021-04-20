<?php

namespace Morebec\Orkestra\Retry\Handler;

use Morebec\Orkestra\Retry\RetryContext;

/**
 * Handler that does nothing when an error happens.
 * To use with {@link RetryStrategy}.
 */
class DoNothingOnError
{
    public function __invoke(RetryContext $context, \Throwable $throwable): void
    {
    }
}
