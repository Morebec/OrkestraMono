<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Represents additional data stored about an event outside of its
 * schema.
 */
class EventMetadata
{
    protected array $data;

    /**
     * EventMetadata constructor.
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
        return $this->data[$key] ?? $defaultValue;
    }

    /**
     * Indicates if a key exists within this metadata.
     */
    public function hasKey(string $key): bool
    {
        return \array_key_exists($key, $this->data);
    }

    /**
     * Returns an array representation of this metadata.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
