<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Projection;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventMetadata;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\EventSourcing\Projection\AbstractDenormalizingProjector;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use PHPUnit\Framework\TestCase;

class AbstractDenormalizingProjectorTest extends TestCase
{
    public function testProject(): void
    {
        $normalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $projector = $this->createProjector($normalizer);

        $normalizer->expects($this->once())->method('denormalize');

        $projector->project(
            new RecordedEventDescriptor(
            EventId::fromString('event_id'),
            EventType::fromString('test-event'),
            new EventMetadata(),
            new EventData(),
            EventStreamId::fromString('stream'),
            EventStreamVersion::fromInt(2),
            EventSequenceNumber::fromInt(2),
            DateTime::now()
        )
        );

        $this->assertTrue($projector->eventDenormalized);
    }

    private function createProjector(MessageNormalizerInterface $messageNormalizer): AbstractDenormalizingProjector
    {
        return new class($messageNormalizer) extends AbstractDenormalizingProjector {
            public $eventDenormalized = false;

            protected function projectEvent(?DomainEventInterface $event, RecordedEventDescriptor $eventDescriptor): void
            {
                $this->eventDenormalized = true;
            }

            public function boot(): void
            {
            }

            public function shutdown(): void
            {
            }

            public function reset(): void
            {
            }

            public static function getTypeName(): string
            {
                return 'projector';
            }
        };
    }
}
