<?php

namespace Morebec\Orkestra\Retry\Handler;

use Morebec\Orkestra\Retry\RetryContext;

/**
 * Handler that only retries if the throwable is an instance of a particular class.
 * To use with {@link RetryStrategy}.
 */
class RetryIfThrowableIsNotInstanceOf
{
    /**
     * @var string
     */
    private $throwableClassName;

    public function __construct(string $throwableClassName)
    {
        $this->throwableClassName = $throwableClassName;
    }

    public function __invoke(RetryContext $retryStrategy, \Throwable $throwable): bool
    {
        return !is_a($throwable, $this->throwableClassName, true);
    }

    public static function className(string $throwableClassName): self
    {
        return new self($throwableClassName);
    }
}
