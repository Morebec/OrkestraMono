<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Represents the data of an event, the data contained should only be scalar values, arrays or null
 * for serialization purposes.
 */
class EventData
{
    /** @var mixed[] */
    protected $data;

    /**
     * EventMetadata constructor.
     *
     * @param mixed[] $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Returns the value of a given key or a default value if it does not exist.
     *
     * @param null $defaultValue
     *
     * @return mixed
     */
    public function getValue(string $key, $defaultValue = null)
    {
        if (!\array_key_exists($key, $this->data)) {
            return $defaultValue;
        }

        return $this->data[$key];
    }

    /**
     * Indicates if a key exists within this data.
     */
    public function hasKey(string $key): bool
    {
        return \array_key_exists($key, $this->data);
    }

    /**
     * Returns an array representation of this data.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
