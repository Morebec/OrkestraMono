<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/**
 * Represents additional data stored about an event outside of its
 * schema.
 */
class MutableEventMetadata extends EventMetadata
{
    /**
     * Add a new value to this metadata.
     * If the key already exists, its previous value gets overwritten.
     */
    public function putValue(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Removes a key from this metadata.
     * If the key does not exists, silently returns.
     */
    public function removeKey(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Returns a copy of this metadata.
     */
    public function copy(): self
    {
        return new self($this->data);
    }
}
