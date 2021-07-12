<?php

namespace Tests\Morebec\Orkestra\EventSourcing\EventProcessor;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\EventSourcing\EventProcessor\MessageBusEventPublisher;
use Morebec\Orkestra\EventSourcing\EventStore\EventDescriptorBuilder;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use Morebec\Orkestra\Messaging\Authorization\UnauthorizedException;
use Morebec\Orkestra\Messaging\Authorization\UnauthorizedResponse;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationException;
use PHPUnit\Framework\TestCase;

class MessageBusEventPublisherTest extends TestCase
{
    public function testPublishEventWithDenormalizationErrorThrowsException(): void
    {
        // Publish we non deserializable.
        $messageBus = $this->getMockBuilder(MessageBusInterface::class)->getMock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();

        $publisher = new MessageBusEventPublisher($messageBus, $messageNormalizer);

        $this->expectException(DenormalizationException::class);
        $publisher->publishEvent(RecordedEventDescriptor::fromEventDescriptor(
            EventDescriptorBuilder::create()
                ->withId(uniqid('evt_', false))
                ->withType('event.tested')
                ->withData(['hello' => 'world'])
                ->build(),
            EventStreamId::fromString('hello'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            new DateTime()
        ));
    }

    public function testPublishEventWithThrowExceptionEnabled(): void
    {
        $messageBus = $this->getMockBuilder(MessageBusInterface::class)->getMock();
        $messageBus->method('sendMessage')->willReturn(
            new UnauthorizedResponse(new UnauthorizedException('Test unauthorized.'))
        );

        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageNormalizer->method('denormalize')->willReturn($this->getMockBuilder(DomainEventInterface::class)->getMock());

        $publisher = new MessageBusEventPublisher($messageBus, $messageNormalizer, true);

        $this->expectException(UnauthorizedException::class);
        $publisher->publishEvent(RecordedEventDescriptor::fromEventDescriptor(
            EventDescriptorBuilder::create()
                ->withId(uniqid('evt_', false))
                ->withType('event.tested')
                ->withData(['hello' => 'world'])
                ->build(),
            EventStreamId::fromString('hello'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            new DateTime()
        ));
    }
}
