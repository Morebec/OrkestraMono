<?php

namespace Morebec\Orkestra\Messaging\Domain\Event;

use Morebec\Orkestra\Messaging\Domain\DomainMessageInterface;

/**
 * Represents a event message. Event Messages are used to communicate
 * that something meaningful has happened.
 */
interface DomainEventInterface extends DomainMessageInterface
{
}
