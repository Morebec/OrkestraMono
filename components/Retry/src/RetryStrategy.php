<?php

namespace Morebec\Orkestra\Retry;

use Morebec\Orkestra\Retry\Handler\AlwaysRetry;
use Morebec\Orkestra\Retry\Handler\DoNothingOnError;
use Morebec\Orkestra\Retry\Handler\ExponentialBackoffDelay;
use Morebec\Orkestra\Retry\Handler\FixedDelay;
use Morebec\Orkestra\Retry\Handler\NoDelay;
use Morebec\Orkestra\Retry\Handler\RetryIfThrowableIsInstanceOf;
use Morebec\Orkestra\Retry\Handler\RetryIfThrowableIsNotInstanceOf;

class RetryStrategy implements RetryStrategyInterface
{
    public const INFINITE_RETRY = -1;

    /**
     * The maximum attempt corresponds to the maximum number of *retry attempts in case of failure*.
     * This means that if an operation fails every time and the maximum attempt is 3 the total number of
     * time the operation will be executed will be 4.
     * Once for the normal flow + 3 for the maximumAttempts value.
     *
     * @var int
     */
    protected $maximumAttempts;

    /**
     * @var callable
     */
    protected $retryConditionFun;

    /**
     * @var callable
     */
    protected $onErrorFun;

    /**
     * @var callable
     */
    protected $retryAfterFun;

    public function __construct(
        int $maximumAttempts,
        callable $retryConditionFun,
        callable $retryAfterFun,
        callable $onErrorFun
    ) {
        $this->maximumAttempts = $maximumAttempts;
        $this->retryConditionFun = $retryConditionFun;
        $this->retryAfterFun = $retryAfterFun;
        $this->onErrorFun = $onErrorFun;
    }

    public static function create(): self
    {
        return new self(
            self::INFINITE_RETRY,
            new AlwaysRetry(),
            new NoDelay(),
            new DoNothingOnError(),
        );
    }

    public function execute(callable $fun)
    {
        $throwable = null;
        for ($attemptNumber = 0; $attemptNumber <= $this->maximumAttempts || $attemptNumber === self::INFINITE_RETRY; $attemptNumber++) {
            $context = new RetryContext($attemptNumber, $this->maximumAttempts);
            try {
                return $fun();
            } catch (\Throwable $t) {
                $throwable = $t;
                ($this->onErrorFun)($context, $throwable);
                if (!($this->retryConditionFun)($context, $throwable)) {
                    return null;
                }
                $delayInMillis = ($this->retryAfterFun)($context, $throwable);

                // If we foresee a next retry let's sleep, otherwise no need to sleep.
                if ($attemptNumber + 1 <= $this->maximumAttempts || $delayInMillis === 0) {
                    // Values greater than one second may not be supported by the system when using usleep
                    // In that case we'll use sleep.
                    if ($delayInMillis < 1000) {
                        usleep($delayInMillis * 1000);
                    } else {
                        sleep($delayInMillis / 1000);
                    }
                }
            }
        }

        throw $throwable;
    }

    public function maximumAttempts(int $maxAttempts): self
    {
        return new self($maxAttempts, $this->retryConditionFun, $this->retryAfterFun, $this->onErrorFun);
    }

    public function retryIf(callable $fun): self
    {
        return new self(
            $this->maximumAttempts,
            $fun,
            $this->retryAfterFun,
            $this->onErrorFun
        );
    }

    public function retryIfInstanceOf(string $className): self
    {
        return self::retryIf(RetryIfThrowableIsInstanceOf::className($className));
    }

    public function retryIfNotInstanceOf(string $className): self
    {
        return self::retryIf(RetryIfThrowableIsNotInstanceOf::className($className));
    }

    public function retryAfter(callable $fun): self
    {
        return new self(
            $this->maximumAttempts,
            $this->retryConditionFun,
            $fun,
            $this->onErrorFun
        );
    }

    public function retryAfterDelay(int $nbMillis): self
    {
        return self::retryAfter(FixedDelay::of($nbMillis));
    }

    public function useExponentialBackoff(
        int $baseInMs = 10,
        float $backOffRate = 2.0,
        int $maxWaitTimeMs = 1000 * 60,
        int $jitterMin = 10,
        int $jitterMax = 10
    ): self {
        return self::retryAfter(new ExponentialBackoffDelay($baseInMs, $backOffRate, $maxWaitTimeMs, $jitterMin, $jitterMax));
    }

    public function onError(callable $fun): self
    {
        return new self($this->maximumAttempts, $this->retryConditionFun, $this->retryAfterFun, $fun);
    }
}
