<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Represents additional data stored about an event outside of its
 * schema.
 */
class EventMetadata
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
