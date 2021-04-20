<?php

namespace Morebec\Orkestra\Messaging\Domain\Event;

use Morebec\Orkestra\Messaging\Domain\DomainMessageHandlerInterface;

/**
 * Represents a message handler specialized in messages of type {@link DomainEventInterface}.
 */
interface DomainEventHandlerInterface extends DomainMessageHandlerInterface
{
}
