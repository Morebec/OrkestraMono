<?php

namespace Morebec\Orkestra\Messaging\Domain\Command;

use Morebec\Orkestra\Messaging\Domain\DomainMessageHandlerInterface;

/**
 * Represents a message handler specialized in messages of type {@link DomainCommandInterface}.
 */
interface DomainCommandHandlerInterface extends DomainMessageHandlerInterface
{
}
