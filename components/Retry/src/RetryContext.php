<?php

namespace Morebec\Orkestra\Retry;

/**
 * Provides information to callbacks about the current context.
 * To use with the {@link RetryStrategy}.
 */
class RetryContext
{
    /** @var int */
    public $attemptNumber;

    /** @var int */
    public $maxAttempt;

    public function __construct(int $attemptNumber, int $maxAttempt)
    {
        $this->attemptNumber = $attemptNumber;
        $this->maxAttempt = $maxAttempt;
    }

    public function getAttemptNumber(): int
    {
        return $this->attemptNumber;
    }

    public function getRetryNumber(): int
    {
        return $this->attemptNumber - 1;
    }

    public function getMaxAttempt(): int
    {
        return $this->maxAttempt;
    }

    public function isLastAttempt(): bool
    {
        return $this->attemptNumber === $this->maxAttempt;
    }
}
