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
use Morebec\Orkestra\EventSourcing\Projection\EventHandlerMethodResolvingProjectorTrait;
use Morebec\Orkestra\EventSourcing\Projection\ProjectorInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use PHPUnit\Framework\TestCase;
use Tests\Morebec\Orkestra\EventSourcing\TestEvent;

class EventHandlerMethodResolvingProjectorTraitTest extends TestCase
{
    public function testProjectEvent(): void
    {
        $normalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $projector = $this->createProjector($normalizer);

        $normalizer->method('denormalize')->willReturn(new TestEvent());

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

        $this->assertTrue($projector->handlerCalledWithOneParams);
        $this->assertTrue($projector->handlerCalledWithTwoParams);
    }

    public function createProjector(MessageNormalizerInterface $messageNormalizer): ProjectorInterface
    {
        return new class($messageNormalizer) extends AbstractDenormalizingProjector {
            use EventHandlerMethodResolvingProjectorTrait;

            public $handlerCalledWithOneParams = false;

            public $handlerCalledWithTwoParams = false;

            public function boot(): void
            {
            }

            public function shutdown(): void
            {
            }

            public function reset(): void
            {
            }

            public function onTestEventWithOneParams(TestEvent $event): void
            {
                $this->handlerCalledWithOneParams = true;
            }

            public function onTestEventWithTwoParams(TestEvent $event, RecordedEventDescriptor $eventDescriptor): void
            {
                $this->handlerCalledWithTwoParams = true;
            }

            public static function getTypeName(): string
            {
                return 'test.denormalizer';
            }
        };
    }
}
