<?php

namespace Morebec\Orkestra\OrkestraServer\Command;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\Messaging\Timeout\MessageBusTimeoutPublisher;
use Morebec\Orkestra\Messaging\Timeout\PollingTimeoutProcessor;
use Morebec\Orkestra\Messaging\Timeout\PollingTimeoutProcessorOptions;
use Morebec\Orkestra\Messaging\Timeout\TimeoutStorageInterface;
use Morebec\Orkestra\SymfonyBundle\Command\AbstractInterruptibleConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MainTimerProcessorConsoleCommand extends AbstractInterruptibleConsoleCommand
{
    protected static $defaultName = 'orkestra:timeout-processor';
    /**
     * @var MessageBusTimeoutPublisher
     */
    private $timeoutPublisher;

    /**
     * @var ClockInterface
     */
    private $clock;
    /**
     * @var TimeoutStorageInterface
     */
    private $timeoutStorage;

    public function __construct(
        MessageBusTimeoutPublisher $timeoutPublisher,
        ClockInterface $clock,
        TimeoutStorageInterface $timeoutStorage
    ) {
        parent::__construct();
        $this->timeoutPublisher = $timeoutPublisher;
        $this->clock = $clock;
        $this->timeoutStorage = $timeoutStorage;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Timeout Processor');

        $options = new PollingTimeoutProcessorOptions();
        $options->withName('main');
        $options->withMaximumProcessingTime(PollingTimeoutProcessorOptions::INFINITE);
        $processor = new PollingTimeoutProcessor($this->clock, $this->timeoutPublisher, $this->timeoutStorage, $options);

        $io->writeln('Timeout Processor Started.');
        $processor->start();

        $io->writeln('Timeout Processor Stopped.');

        return self::SUCCESS;
    }

    protected function onInterruption($input, $output): void
    {
        $output->writeln('Timeout Processor Stopping ...');
    }
}
