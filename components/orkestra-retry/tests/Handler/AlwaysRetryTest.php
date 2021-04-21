<?php

namespace Tests\Morebec\Orkestra\Retry\Handler;

use Morebec\Orkestra\Retry\Handler\AlwaysRetry;
use Morebec\Orkestra\Retry\RetryContext;
use PHPUnit\Framework\TestCase;

class AlwaysRetryTest extends TestCase
{
    public function testInvoke(): void
    {
        $this->assertTrue((new AlwaysRetry())(new RetryContext(1, 5), new \RuntimeException()));
    }
}
