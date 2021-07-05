<?php

namespace Morebec\Orkestra\Worker;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract implementation of a {@link Command} to run a worker.
 */
abstract class AbstractRunWorkerConsoleCommand extends Command implements SignalableCommandInterface
{
    protected WorkerInterface $worker;

    public function __construct(WorkerInterface $worker, string $name)
    {
        parent::__construct($name);
        $this->worker = $worker;
    }

    public function getSubscribedSignals(): array
    {
        return [\SIGINT, \SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        $this->worker->stop();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->worker->start();

        return self::SUCCESS;
    }
}
