<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Returns the options for setting up a Subscriber {@link SubscriptionOptions}.
 */
class SubscriptionOptions
{
    public const POSITION_START = ReadStreamOptions::POSITION_START;
    public const POSITION_END = ReadStreamOptions::POSITION_END;

    /**
     * Position from which the reading should be performed.
     * Depending on the stream it is either a sequence number for the global stream or a version number for all the other streams.
     * The event corresponding to this position will not be included in the {@link StreamedEventCollectionInterface}.
     * If the stream read is the Global Stream, this property corresponds to the sequence number.
     *
     * In essence this option serves as an offset to the read operation.
     *
     * @var int
     */
    public $position = self::POSITION_START;

    /**
     * Sugar syntax function.
     */
    public static function subscribe(): self
    {
        return new self();
    }

    public function fromStart(): self
    {
        $this->position = self::POSITION_START;

        return $this;
    }

    public function fromEnd(): self
    {
        $this->position = self::POSITION_END;

        return $this;
    }

    public function from(int $position): self
    {
        if ($position < 0) {
            if ($position !== self::POSITION_START && $position !== self::POSITION_END) {
                throw new \InvalidArgumentException('The position cannot be a negative number');
            }
        }
        $this->position = $position;

        return $this;
    }
}
