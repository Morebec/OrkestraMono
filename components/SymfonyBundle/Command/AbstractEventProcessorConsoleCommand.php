<?php

namespace Morebec\OrkestraSymfonyBundle\Command;

use Doctrine\DBAL\Exception;
use Morebec\Orkestra\EventSourcing\EventProcessor\ListenableEventProcessorListenerInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\ReplayableEventProcessorInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\TrackingEventProcessor;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\ReadStreamOptions;
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

class AbstractEventProcessorConsoleCommand extends Command implements ListenableEventProcessorListenerInterface
{
    /** @var SymfonyStyle */
    protected $io;

    /** @var ProgressBar */
    protected $replayProgressBar;

    /** @var string|null */
    protected $displayName;

    /** @var ReplayableEventProcessorInterface */
    private $processor;

    /** @var string */
    private $progressBarStyle;

    public function __construct(ReplayableEventProcessorInterface $processor, string $commandName = null, string $displayName = null, string $progressBarStyle = 'modern')
    {
        parent::__construct($commandName);
        $this->displayName = $displayName ?: 'Event Processor';

        $this->progressBarStyle = $progressBarStyle;

        $this->processor = $processor;
        $this->processor->addListener($this);
    }

    public function onStart(TrackingEventProcessor $processor): void
    {
        $this->io->writeln('Processor Started');
    }

    public function onStop(TrackingEventProcessor $processor): void
    {
        $this->io->writeln('Processor Stopped.');
    }

    public function beforeEvent(TrackingEventProcessor $processor, RecordedEventDescriptor $eventDescriptor): void
    {
    }

    public function afterEvent(TrackingEventProcessor $processor, RecordedEventDescriptor $eventDescriptor): void
    {
        if ($this->replayProgressBar) {
            $this->replayProgressBar->advance();
        }
    }

    protected function configure()
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

        $this->io->title($this->displayName);

        $command = $input->getArgument('processor-command');

        return $this->executeCommand($command);
    }

    /**
     * @param $command
     *
     * @throws Exception
     */
    protected function executeCommand($command): int
    {
        switch ($command) {
            case 'replay':
                $this->replayProcessor();
                break;

            case 'reset':
                $this->resetProcessor();
                break;

            case 'start':
                $this->startProcessor();
                break;

            case 'progress':
                $this->displayProgress();
                break;

            case 'list-commands':
                $this->displayHelp();
                break;

            default:
                $this->io->error(sprintf('Command "%s" is not defined.', $command));
                $this->displayHelp();

                return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function displayHelp(): void
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

        if ($this->processor instanceof ReplayableEventProcessorInterface) {
            $rows[] = ['replay', 'Replays the processor form the beginning of the stream.'];
        }

        if ($this->processor instanceof TrackingEventProcessor) {
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

    protected function startProcessor(): void
    {
        $this->processor->start();
    }

    protected function replayProcessor(): void
    {
        if (!($this->processor instanceof ReplayableEventProcessorInterface)) {
            throw new \LogicException('This processor cannot be replayed.');
        }

        if ($this->processor instanceof TrackingEventProcessor) {
            $this->io->writeln('Resetting processor ...');
            $this->processor->reset();
        }

        $events = $this->processor->getNextEvents();
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

        $this->processor->replay();
        $this->replayProgressBar->finish();
        $this->io->success('Replay completed.');

        $this->processor->stop();
    }

    protected function resetProcessor(): void
    {
        if (!($this->processor instanceof TrackingEventProcessor)) {
            $this->io->warning('This processor cannot be reset.');

            return;
        }

        $this->io->writeln('Resetting processor ...');
        $this->processor->reset();
        $this->io->success('Reset completed.');
    }

    /**
     * @throws Exception
     */
    protected function displayProgress(): void
    {
        if (!$this->processor instanceof TrackingEventProcessor) {
            $this->io->warning('No progress to be displayed.');

            return;
        }

        $streamId = $this->processor->getStreamId();
        $eventStore = $this->processor->getEventStore();
        if ($streamId->isEqualTo($eventStore->getGlobalStreamId())) {
            $firstEvent = $eventStore->readStream($streamId, ReadStreamOptions::firstEvent())->getFirst();
            $firstPosition = $firstEvent ? $firstEvent->getSequenceNumber()->toInt() : -1;

            $lastEvent = $eventStore->readStream($streamId, ReadStreamOptions::lastEvent())->getLast();
            $lastPosition = $lastEvent ? $lastEvent->getSequenceNumber()->toInt() : -1;
        } else {
            $stream = $eventStore->getStream($streamId);
            $streamVersion = $stream ? $stream->getVersion()->toInt() : 0;
            $firstPosition = $streamVersion === EventStreamVersion::INITIAL_VERSION ? EventStreamVersion::INITIAL_VERSION : $streamVersion;
            $lastPosition = $streamVersion;
        }

        $totalNbEvents = $lastPosition - $firstPosition;

        $currentPosition = $this->processor->getPosition();

        $nbEventsProcessed = $currentPosition == EventStreamVersion::INITIAL_VERSION ? 0 : $currentPosition - $firstPosition;
        $nbEventsToBeProcessed = $totalNbEvents - $nbEventsProcessed;

        $progress = round($nbEventsProcessed / ($totalNbEvents !== 0 ? $totalNbEvents : 1) * 100, 2);

        $this->io->writeln(sprintf('Stream ID: <info>%s</info>', $streamId));
        $this->io->writeln("Last stream position: <info>$lastPosition</info>");
        $this->io->writeln("Current stream position: <info>$currentPosition</info>");
        $this->io->writeln("Total events: <info>$totalNbEvents</info>");
        $this->io->writeln("Events Processed: <info>$nbEventsProcessed</info>");
        $this->io->writeln("Events to process: <info>$nbEventsToBeProcessed</info>");
        $this->io->writeln("Progress: $nbEventsProcessed / $totalNbEvents <info>($progress %)</info>");
    }
}
