<?php

namespace Morebec\Orkestra\EventSourcing\Projection;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;

/**
 * Implementation of a {@link ProjectorInterface} that is capable of denormalizing RecordedEventDescriptors,
 * and working on this denormalized form.
 */
abstract class AbstractDenormalizingProjector implements ProjectorInterface
{
    /**
     * @var MessageNormalizerInterface
     */
    private $messageNormalizer;

    public function __construct(MessageNormalizerInterface $messageNormalizer)
    {
        $this->messageNormalizer = $messageNormalizer;
    }

    public function project(RecordedEventDescriptor $descriptor): void
    {
        /** @var DomainEventInterface $event */
        $event = $this->messageNormalizer->denormalize($descriptor->getEventData()->toArray(), $descriptor->getEventType());
        $this->projectEvent($event, $descriptor);
    }

    /**
     * Projects a Denormalized Domain Event.
     */
    abstract protected function projectEvent(?DomainEventInterface $event, RecordedEventDescriptor $eventDescriptor): void;
}
