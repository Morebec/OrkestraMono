<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

class TruncateStreamOptions
{
    /**
     * Version number from which the truncating should be performed in the given stream.
     */
    public EventStreamVersion $beforeVersionNumber;

    public static function beforeVersionNumber(EventStreamVersion $versionNumber): self
    {
        $options = new self();

        if ($versionNumber->toInt() < 0) {
            throw new \InvalidArgumentException("The version number must be a positive integer or 0, got {$versionNumber->toInt()}");
        }

        $options->beforeVersionNumber = $versionNumber;

        return $options;
    }
}
