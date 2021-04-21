<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

use Morebec\Orkestra\Enum\Enum;

/**
 * Represents the possible directions when reading a stream.
 *
 * @method static self FORWARD()
 * @method static self BACKWARD()
 */
class ReadStreamDirection extends Enum
{
    /** @var string From oldest to newest. */
    public const FORWARD = 'FORWARD';

    /** @var string From newest to oldest. */
    public const BACKWARD = 'BACKWARD';
}
