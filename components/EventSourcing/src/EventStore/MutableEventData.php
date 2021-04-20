<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Mutable implementation of EventData.
 */
class MutableEventData extends EventData
{
    /**
     * Add a new value to this data.
     * If the key already exists, its previous value gets overwritten.
     */
    public function putValue(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Removes a key from this data.
     * If the key does not exists, silently returns.
     */
    public function removeKey(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Returns a copy of this data.
     */
    public function copy(): self
    {
        return new self($this->data);
    }
}
