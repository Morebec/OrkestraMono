<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\DateTime\DateTime;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * A Timeout is a specific type of message that indicates that something should be happening
 * at a given time in the future. Timeouts are sent to the message bus at their triggering time.
 */
interface TimeoutInterface extends MessageInterface
{
    /**
     * Returns the Id of this schedule.
     */
    public function getId(): string;

    /**
     * Returns the date time at which this schedule ends.
     */
    public function getEndsAt(): DateTime;
}
