<?php

namespace Tests\Morebec\Orkestra\Worker;

use Morebec\Orkestra\Worker\AbstractTimedWorker;
use PHPUnit\Framework\TestCase;

class AbstractTimedWorkerTest extends TestCase
{
    public function testWorker(): void
    {
        $executionInterval = 100;
        $maxExecutionCount = 5;

        $worker = new class($executionInterval, $maxExecutionCount) extends AbstractTimedWorker {
            private int $maxExecutionCount;

            public function __construct(int $executionInterval, int $maxExecutionCount)
            {
                parent::__construct($executionInterval);
                $this->maxExecutionCount = $maxExecutionCount;
            }

            public function doWork(): void
            {
                if ($this->executionCount === $this->maxExecutionCount) {
                    $this->stop();
                }
            }
        };

        $t1 = round(microtime(true) * 1000);

        $worker->start();

        $t2 = round(microtime(true) * 1000);

        $actualExecutionTime = $t2 - $t1;
        $expectedExecutionTime = $executionInterval * $maxExecutionCount;

        self::assertTrue($actualExecutionTime >= $expectedExecutionTime);
        self::assertTrue($actualExecutionTime < $expectedExecutionTime + 100);
    }
}
