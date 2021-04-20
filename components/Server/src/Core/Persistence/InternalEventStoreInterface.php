<?php

namespace Morebec\Orkestra\OrkestraServer\Core\Persistence;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;

/**
 * Interface representing the event store internal to the server.
 */
interface InternalEventStoreInterface extends EventStoreInterface
{
}
