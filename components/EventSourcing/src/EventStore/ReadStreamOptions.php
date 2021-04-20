<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Defines the options for reading a stream.
 * TODO: Add Tests.
 */
class ReadStreamOptions
{
    public const POSITION_START = -1;
    public const POSITION_END = -2;

    /**
     * The direction into which to perform the read operation. (Default forward).
     *
     * @var ReadStreamDirection
     */
    public $direction;

    /**
     * The maximum number of events to return for a given read operation.
     *
     * @var int|null
     */
    public $maxCount = 1000;

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

    public function __construct()
    {
        $this->direction = ReadStreamDirection::FORWARD();
        $this->maxCount = 1000;
        $this->position = self::POSITION_START;
    }

    /**
     * Sugar syntax function.
     */
    public static function read(): self
    {
        return new self();
    }

    /**
     * Preconfigured option values to return that last event of the stream.
     */
    public static function lastEvent(): self
    {
        return (new self())
            ->backward()
            ->fromEnd()
            ->maxCount(1)
        ;
    }

    /**
     * Preconfigured option values to return the first event of the stream.
     */
    public static function firstEvent(): self
    {
        return (new self())
            ->forward()
            ->fromStart()
            ->maxCount(1)
        ;
    }

    public function forward(): self
    {
        $this->direction = ReadStreamDirection::FORWARD();

        return $this;
    }

    public function backward(): self
    {
        $this->direction = ReadStreamDirection::BACKWARD();

        return $this;
    }

    public function direction(ReadStreamDirection $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function maxCount(?int $count): self
    {
        $this->maxCount = $count;

        return $this;
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
