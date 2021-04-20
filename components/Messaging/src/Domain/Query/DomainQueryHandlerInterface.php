<?php

namespace Morebec\Orkestra\Messaging\Domain\Query;

use Morebec\Orkestra\Messaging\Domain\DomainMessageHandlerInterface;

/**
 * Represents a message handler specialized in messages of type {@link DomainQueryInterface}.
 */
interface DomainQueryHandlerInterface extends DomainMessageHandlerInterface
{
}
