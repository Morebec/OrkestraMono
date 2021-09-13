<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Psr\Log\LoggerInterface;

class LoggingTrackingEventProcessorListener implements TrackingEventProcessorListenerInterface
{
    private LoggerInterface $logger;
    private bool $logBeforeEvent;
    private bool $logAfterEvent;

    public function __construct(LoggerInterface $logger, bool $logBeforeEvent = false, bool $logAfterEvent = false)
    {
        $this->logger = $logger;
        $this->logBeforeEvent = $logBeforeEvent;
        $this->logAfterEvent = $logAfterEvent;
    }

    public function onStart(ListenableEventProcessorInterface $processor): void
    {
        $this->logger->info('Started Event Processor {eventProcessorName}.', [
            'eventProcessorName' => $processor->getName(),
        ]);
    }

    public function onStop(ListenableEventProcessorInterface $processor): void
    {
        $this->logger->info('Stopped Event Processor {eventProcessorName}.', [
            'eventProcessorName' => $processor->getName(),
        ]);
    }

    public function beforeEvent(ListenableEventProcessorInterface $processor, RecordedEventDescriptor $eventDescriptor): void
    {
        if (!$this->logBeforeEvent) {
            return;
        }

        $this->logger->info('Event Processor {eventProcessorName} will process event {eventId}.', [
            'eventProcessorName' => $processor->getName(),
            'eventId' => (string) $eventDescriptor->getEventId(),
            'streamId' => (string) $eventDescriptor->getStreamId(),
            'sequenceNumber' => $eventDescriptor->getSequenceNumber()->toInt(),
        ]);
    }

    public function afterEvent(ListenableEventProcessorInterface $processor, RecordedEventDescriptor $eventDescriptor): void
    {
        if (!$this->logAfterEvent) {
            return;
        }

        $this->logger->info('Event Processor {eventProcessorName} has processed event {eventId}.', [
            'eventProcessorName' => $processor->getName(),
            'eventId' => (string) $eventDescriptor->getEventId(),
            'streamId' => (string) $eventDescriptor->getStreamId(),
            'sequenceNumber' => $eventDescriptor->getSequenceNumber()->toInt(),
        ]);
    }

    public function onReset(TrackingEventProcessor $processor): void
    {
        $this->logger->info('Reset Event Processor {eventProcessorName}.', [
            'eventProcessorName' => $processor->getName(),
        ]);
    }
}
