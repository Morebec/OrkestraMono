<?php

namespace Morebec\Orkestra\Retry\Handler;

/**
 * Handler that does not return a delay to retry.
 * To use with {@link RetryStrategy}.
 */
class NoDelay extends FixedDelay
{
    public function __construct()
    {
        parent::__construct(0);
    }
}
