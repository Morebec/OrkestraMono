<?php

namespace Morebec\OrkestraSymfonyBundle\Command;

use Morebec\Orkestra\Messaging\Timeout\TimeoutProcessorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractTimeoutProcessorConsoleCommand extends Command implements SignalableCommandInterface
{
    protected static $defaultName = 'orkestra:timeout-processor';

    /**
     * Initialized on execute.
     */
    private SymfonyStyle $io;

    public function getSubscribedSignals(): array
    {
        return [\SIGTERM, \SIGINT];
    }

    public function handleSignal(int $signal): void
    {
        $this->io->writeln('Timeout Processor Stopping ...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Timeout Processor');

        $processor = $this->getProcessor();

        $this->io->writeln('Timeout Processor Started.');
        $processor->start();

        $this->io->writeln('Timeout Processor Stopped.');

        return self::SUCCESS;
    }

    /**
     * Returns the processor to use for this console command.
     */
    abstract protected function getTimeoutProcessor(): TimeoutProcessorInterface;
}
