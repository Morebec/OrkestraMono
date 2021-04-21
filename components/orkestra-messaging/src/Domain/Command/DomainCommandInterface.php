<?php

namespace Morebec\Orkestra\Messaging\Domain\Command;

use Morebec\Orkestra\Messaging\Domain\DomainMessageInterface;

/**
 * Interface representing a Command Message.
 * Commands a used to request a certain action be taken by some unit.
 */
interface DomainCommandInterface extends DomainMessageInterface
{
}
