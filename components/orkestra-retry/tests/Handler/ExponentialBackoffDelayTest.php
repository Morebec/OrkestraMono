<?php

namespace Tests\Morebec\Orkestra\Retry\Handler;

use Morebec\Orkestra\Retry\Handler\ExponentialBackoffDelay;
use Morebec\Orkestra\Retry\RetryContext;
use PHPUnit\Framework\TestCase;

class ExponentialBackoffDelayTest extends TestCase
{
    public function testInvoke(): void
    {
        $h = new ExponentialBackoffDelay(10, 2, 1000, 0, 0);

        $delays = [];
        for ($i = 0; $i < 10; $i++) {
            $delays[] = $h(new RetryContext($i, 5), new \RuntimeException());
        }

        $this->assertEquals([
            0, 20, 40, 80, 160, 320, 640, 1000, 1000, 1000,
        ], $delays);
    }
}
