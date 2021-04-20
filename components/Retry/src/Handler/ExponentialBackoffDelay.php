<?php

namespace Morebec\Orkestra\Retry\Handler;

use Morebec\Orkestra\Retry\RetryContext;

/**
 * Retry handler that performs an exponential backoff in the delay required to wait between retry attempts.
 * An exponential backoff is a retry strategy policy where a computational unit retries a failed operation
 * with increasing delays (exponential so) between retries.
 *
 * The algorithm it uses is the following:
 * sleep = min(cap, base * backoffRate ** attemptNumber) + random_between(jitterMin, jitterMax)
 *
 * Where
 * - `base` is the minimum amount of time to be waiting.
 * - `backoffRate`: base exponential rate
 * - jitterMin: minimum randomness amplitude added to the sleep time to avoid clustered synchronized calls
 * - jitterMax: maximum randomness amplitude added to the sleep time to avoid clustered synchronized calls
 */
class ExponentialBackoffDelay
{
    /**
     * @var int
     */
    private $maxWaitTimeMs;

    /**
     * @var int
     */
    private $interval;
    /**
     * @var float
     */
    private $backOffRate;
    /**
     * @var int
     */
    private $jitterMin;
    /**
     * @var int
     */
    private $jitterMax;

    public function __construct(int $baseInMs = 10, float $backOffRate = 2.0, int $maxWaitTimeMs = 1000 * 60, int $jitterMin = 10, int $jitterMax = 10)
    {
        $this->maxWaitTimeMs = $maxWaitTimeMs;

        $this->interval = $baseInMs;
        $this->backOffRate = $backOffRate;
        $this->jitterMin = $jitterMin;
        $this->jitterMax = $jitterMax;
    }

    public function __invoke(RetryContext $retryContext, \Throwable $throwable): int
    {
        $attemptNumber = $retryContext->getAttemptNumber();

        if ($attemptNumber === 0) {
            return 0;
        }

        $waitTime = $this->interval * $this->backOffRate ** $attemptNumber;

        if ($this->maxWaitTimeMs) {
            $waitTime = min($waitTime, $this->maxWaitTimeMs);
        }

        return $waitTime + random_int($this->jitterMin, $this->jitterMax);
    }
}
