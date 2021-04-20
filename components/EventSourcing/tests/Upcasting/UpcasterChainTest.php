<?php

namespace Tests\Morebec\Orkestra\EventSourcing\Upcasting;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\EventSourcing\EventStore\EventData;
use Morebec\Orkestra\EventSourcing\EventStore\EventId;
use Morebec\Orkestra\EventSourcing\EventStore\EventMetadata;
use Morebec\Orkestra\EventSourcing\EventStore\EventSequenceNumber;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;
use Morebec\Orkestra\EventSourcing\EventStore\EventStreamVersion;
use Morebec\Orkestra\EventSourcing\EventStore\EventType;
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractMultiEventUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\AbstractSingleEventUpcaster;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcastableEventDescriptor;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcasterChain;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcasterInterface;
use PHPUnit\Framework\TestCase;

class UpcasterChainTest extends TestCase
{
    public function testUpcast(): void
    {
        $chain = new UpcasterChain([
            $this->getUpcasterA(),
            $this->getUpcasterB(),
        ]);

        $data = [
            'fullname' => 'John Doe',
        ];
        $result = $chain->upcast(new UpcastableEventDescriptor(
            EventId::fromString('event1'),
            EventType::fromString('test.event'),
            new EventMetadata(),
            new EventData($data),
            EventStreamId::fromString('test-stream'),
            EventStreamVersion::initial(),
            EventSequenceNumber::fromInt(0),
            new DateTime()
        ));

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('firstName', $result[0]->getEventData()->toArray());
        $this->assertArrayHasKey('lastName', $result[1]->getEventData()->toArray());
    }

    private function getUpcasterA(): UpcasterInterface
    {
        return new class() extends AbstractSingleEventUpcaster {
            public function __construct()
            {
                parent::__construct('test.event');
            }

            public function supports(UpcastableEventDescriptor $eventDescriptor): bool
            {
                return true;
            }

            protected function doUpcast(UpcastableEventDescriptor $message): UpcastableEventDescriptor
            {
                [$firstName, $lastName] = explode(' ', $message->getEventData()->getValue('fullname'));

                return $message
                    ->withFieldRemoved('fullname')
                    ->withFieldAdded('firstName', $firstName)
                    ->withFieldAdded('lastName', $lastName);
            }
        };
    }

    private function getUpcasterB(): UpcasterInterface
    {
        return new class() extends AbstractMultiEventUpcaster {
            public function __construct()
            {
                parent::__construct('test.event');
            }

            public function doUpcast(UpcastableEventDescriptor $eventDescriptor): array
            {
                return [
                    new UpcastableEventDescriptor(
                        EventId::fromString('event1'),
                        EventType::fromString('test.event'),
                        new EventMetadata(),
                        new EventData(['firstName' => $eventDescriptor->getField('firstName')]),
                        EventStreamId::fromString('test-stream'),
                        EventStreamVersion::initial(),
                        EventSequenceNumber::fromInt(0),
                        new DateTime()
                    ),
                    new UpcastableEventDescriptor(
                        EventId::fromString('event1'),
                        EventType::fromString('test.event'),
                        new EventMetadata(),
                        new EventData(['lastName' => $eventDescriptor->getField('lastName')]),
                        EventStreamId::fromString('test-stream'),
                        EventStreamVersion::initial(),
                        EventSequenceNumber::fromInt(0),
                        new DateTime()
                    ),
                ];
            }

            public function supports(UpcastableEventDescriptor $message): bool
            {
                return true;
            }
        };
    }
}
