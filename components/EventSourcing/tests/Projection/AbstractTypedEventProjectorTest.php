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
use Morebec\Orkestra\EventSourcing\Projection\AbstractTypedEventProjector;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use PHPUnit\Framework\TestCase;
use Tests\Morebec\Orkestra\EventSourcing\TestEvent;

class AbstractTypedEventProjectorTest extends TestCase
{
    public function testEventHandlerMethods(): void
    {
        $projector = $this->createProjector();

        $projector->project(new RecordedEventDescriptor(
            EventId::fromString('event_id'),
            EventType::fromString('test-event'),
            new EventMetadata(),
            new EventData(),
            EventStreamId::fromString('stream'),
            EventStreamVersion::fromInt(2),
            EventSequenceNumber::fromInt(2),
            DateTime::now()
        ));

        $this->assertTrue($projector->eventHandled);
    }

    private function createProjector(): AbstractTypedEventProjector
    {
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageNormalizer->method('denormalize')->willReturn(new TestEvent());

        return new class($messageNormalizer) extends AbstractTypedEventProjector {
            public $eventHandled = false;

            public function onTestEvent(TestEvent $event): void
            {
                $this->eventHandled = true;
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
