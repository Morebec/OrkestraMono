<?php

namespace Tests\Morebec\Orkestra\Retry\Handler;

use Morebec\Orkestra\Retry\Handler\RetryIfThrowableIsInstanceOf;
use Morebec\Orkestra\Retry\RetryContext;
use PHPUnit\Framework\TestCase;

class RetryIfThrowableInstanceOfTest extends TestCase
{
    public function testInvoke(): void
    {
        $r = new RetryIfThrowableIsInstanceOf(\RuntimeException::class);
        $this->assertTrue($r(new RetryContext(1, 5), new \RuntimeException()));
        $this->assertFalse($r(new RetryContext(1, 5), new \LogicException()));
    }
}
