<?php

namespace Morebec\Orkestra\SymfonyBundle\Command;

use Morebec\Orkestra\EventSourcing\EventProcessor\EventProcessorInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventProcessorListenerInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\ListenableEventProcessorInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\TrackingEventProcessor;
use Morebec\Orkestra\EventSourcing\EventProcessor\TrackingEventProcessorInspector;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractEventProcessorConsoleCommand extends Command implements EventProcessorListenerInterface
{
    protected SymfonyStyle $io;

    protected ?ProgressBar $replayProgressBar;

    private string $progressBarStyle;

    public function __construct(
        string $commandName = null,
        string $progressBarStyle = 'modern'
    ) {
        parent::__construct($commandName);

        $this->progressBarStyle = $progressBarStyle;
    }

    public function onStart(ListenableEventProcessorInterface $processor): void
    {
        $this->io->writeln('Processor Started');
    }

    public function onStop(ListenableEventProcessorInterface $processor): void
    {
        $this->io->writeln('Processor Stopped.');
    }

    public function beforeEvent(ListenableEventProcessorInterface $processor, RecordedEventDescriptor $eventDescriptor): void
    {
    }

    public function afterEvent(ListenableEventProcessorInterface $processor, RecordedEventDescriptor $eventDescriptor): void
    {
        if ($this->replayProgressBar) {
            $this->replayProgressBar->advance();
        }
    }

    protected function configure(): void
    {
        $this->addArgument(
            'processor-command',
            InputArgument::REQUIRED,
            'This argument allows to specify a command to perform on the event processor.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $command = $input->getArgument('processor-command');

        $processor = $this->getProcessor($input, $output);

        return $this->executeCommand($processor, $command);
    }

    /**
     * Executes the command passed to this console command (e.g. replay, start, reset etc.)
     * for a given processor.
     *
     * @return int status code
     */
    protected function executeCommand(EventProcessorInterface $processor, string $command): int
    {
        switch ($command) {
            case 'replay':
                $this->replayProcessor($processor);
                break;

            case 'reset':
                $this->resetProcessor($processor);
                break;

            case 'start':
                $this->startProcessor($processor);
                break;

            case 'progress':
                $this->displayProgress($processor);
                break;

            case 'list-commands':
                $this->displayHelp($processor);
                break;

            default:
                $this->io->error(sprintf('Command "%s" is not defined.', $command));
                $this->displayHelp($processor);

                return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Builds and instantiate the Processor.
     */
    abstract protected function getProcessor(InputInterface $input, OutputInterface $output): EventProcessorInterface;

    /**
     * Displays the help message.
     */
    protected function displayHelp(EventProcessorInterface $processor): void
    {
        $help = new HelpCommand();
        $help->setCommand($this);

        $help->run(new ArgvInput(), $this->io);

        $this->io->writeln('');

        $this->io->writeln('<comment>Processor Commands: </>');

        $rows = [
            ['start', 'Allows to run the processor.'],
            ['list-commands', 'Displays the current help message.'],
        ];

        if ($processor instanceof TrackingEventProcessor) {
            $rows[] = ['replay', 'Replays the processor form the beginning of the stream.'];
            $rows[] = ['reset', 'Resets the position storage of the processor.'];
            $rows[] = ['progress', 'Displays the current progress of the processor.'];
        }

        foreach ($rows as $key => $row) {
            $rows[$key][0] = "<info>{$row[0]}</info>";
        }

        $table = new Table($this->io);

        $table->setRows($rows);
        $table->setStyle('borderless');
        $table->getStyle()->setHorizontalBorderChars('');

        $table->render();
    }

    /**
     * Starts the processor.
     */
    protected function startProcessor(EventProcessorInterface $processor): void
    {
        $processor->start();
    }

    /**
     * Replays the processor.
     */
    protected function replayProcessor(EventProcessorInterface $processor): void
    {
        if (!($processor instanceof TrackingEventProcessor)) {
            throw new \LogicException('This processor cannot be replayed.');
        }

        $this->resetProcessor($processor);

        $events = $processor->getNextEvents();
        $this->io->writeln(sprintf('Events to replay <info>%s</info> events ...', \count($events)));

        if ($events->isEmpty()) {
            $this->io->warning('No events available for processing, aborting ...');

            return;
        }

        $nbEvents = \count($events);
        // Free up memory
        unset($events);

        $this->replayProgressBar = new ProgressBar($this->io, $nbEvents);

        if ($this->progressBarStyle === 'modern') {
            $this->replayProgressBar = ProgressBarBuilder::modern($this->io, $nbEvents);
        } elseif ($this->progressBarStyle === 'classic') {
            $this->replayProgressBar = ProgressBarBuilder::classic($this->io, $nbEvents);
        } else {
            $this->replayProgressBar = new ProgressBar($this->io, $nbEvents);
        }

        $processor->replay();
        $this->replayProgressBar->finish();
        $this->io->success('Replay completed.');

        $processor->stop();
    }

    /**
     * Resets the processor.
     */
    protected function resetProcessor(EventProcessorInterface $processor): void
    {
        if (!($processor instanceof TrackingEventProcessor)) {
            $this->io->warning('This processor cannot be reset.');

            return;
        }

        $this->io->writeln('Resetting processor ...');
        $processor->reset();
        $this->io->success('Reset completed.');
    }

    /**
     * Display the progress status of the processor.
     */
    protected function displayProgress(EventProcessorInterface $processor): void
    {
        if (!$processor instanceof TrackingEventProcessor) {
            $this->io->warning('No progress to be displayed.');

            return;
        }

        $inspector = new TrackingEventProcessorInspector();
        $progress = $inspector->inspect($processor);

        $totalNumberEvents = $progress->getTotalNumberEvents();
        $numberEventProcessed = $progress->getNumberEventProcessed();

        $this->io->writeln(sprintf('Stream ID: <info>%s</info>', $progress->getStreamId()));
        $this->io->writeln("Last stream position: <info>{$progress->getLastPosition()}</info>");
        $this->io->writeln("Current stream position: <info>{$progress->getCurrentPosition()}</info>");
        $this->io->writeln("Total events: <info>{$totalNumberEvents}</info>");
        $this->io->writeln("Events Processed: <info>{$numberEventProcessed}</info>");
        $this->io->writeln("Events to process: <info>{$progress->getNumberEventsToProcess()}</info>");
        $this->io->writeln(sprintf('Progress: %s / %s <info>(%s %%)</info>',
                $numberEventProcessed,
                $totalNumberEvents,
            $progress->getProgressPercentage())
        );
    }
}
