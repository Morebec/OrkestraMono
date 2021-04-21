<?php

namespace Morebec\Orkestra\OrkestraServer\Core\Persistence;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;

/**
 * Interface representing the event store used to store the events of registered services.
 */
interface ServiceEventStoreInterface extends EventStoreInterface
{
}
