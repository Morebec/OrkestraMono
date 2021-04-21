<?php

namespace Tests\Morebec\Orkestra\Retry\Handler;

use Morebec\Orkestra\Retry\Handler\FixedDelay;
use Morebec\Orkestra\Retry\RetryContext;
use PHPUnit\Framework\TestCase;

class FixedDelayTest extends TestCase
{
    public function testInvoke(): void
    {
        $delay = FixedDelay::of(5)(new RetryContext(1, 3), new \RuntimeException());
        $this->assertEquals(5, $delay);
    }
}
