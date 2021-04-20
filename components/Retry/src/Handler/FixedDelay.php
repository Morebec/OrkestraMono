<?php

namespace Morebec\Orkestra\Retry\Handler;

use Morebec\Orkestra\Retry\RetryContext;

/**
 * Handler that returns a fixed value as a delay to retry.
 * To use with {@link RetryStrategy}.
 */
class FixedDelay
{
    /**
     * @var int
     */
    private $delay;

    public function __construct(int $delay)
    {
        $this->delay = $delay;
    }

    public function __invoke(RetryContext $context, \Throwable $throwable): int
    {
        return $this->delay;
    }

    public static function of(int $delayInMs): self
    {
        return new self($delayInMs);
    }
}
