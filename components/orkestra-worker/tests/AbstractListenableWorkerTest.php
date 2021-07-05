<?php

namespace Tests\Morebec\Orkestra\Worker;

use Morebec\Orkestra\Worker\AbstractListenableWorker;
use Morebec\Orkestra\Worker\WorkerListenerInterface;
use PHPUnit\Framework\TestCase;

class AbstractListenableWorkerTest extends TestCase
{
    public function test(): void
    {
        $worker = new class() extends AbstractListenableWorker {
            private $running = false;

            protected function doStart(): void
            {
                $this->running = true;
            }

            protected function doStop(): void
            {
                $this->running = false;
            }

            public function isRunning(): bool
            {
                return $this->running;
            }
        };

        $listener = new class() implements WorkerListenerInterface {
            public $startedCount = 0;
            public $stoppedCount = 0;

            public function onStarted(): void
            {
                $this->startedCount++;
            }

            public function onStopped(): void
            {
                $this->stoppedCount++;
            }
        };

        $worker->addListener($listener);

        $worker->start();
        $worker->stop();

        $this->assertEquals(1, $listener->startedCount);
        $this->assertEquals(1, $listener->startedCount);

        $worker->removeListener($listener);

        $worker->start();
        $worker->stop();

        $this->assertEquals(1, $listener->startedCount);
        $this->assertEquals(1, $listener->startedCount);
    }
}
