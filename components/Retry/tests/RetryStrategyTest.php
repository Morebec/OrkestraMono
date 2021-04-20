<?php

namespace Tests\Morebec\Orkestra\Retry;

use Morebec\Orkestra\Retry\Handler\AlwaysRetry;
use Morebec\Orkestra\Retry\RetryContext;
use Morebec\Orkestra\Retry\RetryStrategy;
use PHPUnit\Framework\TestCase;

class RetryStrategyTest extends TestCase
{
    public function testRetryAfter(): void
    {
        $retry = RetryStrategy::create()
            ->maximumAttempts(3)
            ->retryAfterDelay(10)
        ;

        $t1 = microtime(true);

        $nbAttempts = 0;
        $retry->execute(static function () use (&$nbAttempts) {
            $nbAttempts++;
            if ($nbAttempts !== 4) {
                throw new \RuntimeException('FAILED');
            }
        });
        $t2 = microtime(true);
        $timeElapsed = ($t2 - $t1) * 1000;

        // three retries with a wait time of 10ms (at least)
        $this->assertGreaterThanOrEqual(30, (int) $timeElapsed);
    }

    public function testRetryIf(): void
    {
        $retry = RetryStrategy::create()
            ->maximumAttempts(3)
            ->retryIf(new AlwaysRetry())
        ;

        $nbAttempts = 0;
        $retry->execute(static function () use (&$nbAttempts) {
            $nbAttempts++;
            if ($nbAttempts !== 4) {
                throw new \RuntimeException('FAILED');
            }
        });

        $this->assertEquals(4, $nbAttempts);
    }

    public function testOnError(): void
    {
        $onErrorTriggered = false;
        $retry = RetryStrategy::create()
            ->maximumAttempts(3)
            ->onError(static function (RetryContext $context, \Throwable $throwable) use (&$onErrorTriggered) {
                $onErrorTriggered = true;
            });

        $nbAttempts = 0;
        $retry->execute(static function () use (&$nbAttempts) {
            $nbAttempts++;
            if ($nbAttempts !== 4) {
                throw new \RuntimeException('FAILED');
            }
        });

        $this->assertTrue($onErrorTriggered);
    }

    public function testMaximumAttempts(): void
    {
        $retry = RetryStrategy::create()
            ->maximumAttempts(3)
        ;

        $nbAttempts = 0;
        $retry->execute(static function () use (&$nbAttempts) {
            $nbAttempts++;
            if ($nbAttempts !== 4) {
                throw new \RuntimeException('FAILED');
            }
        });

        $this->assertEquals(4, $nbAttempts);
    }
}
