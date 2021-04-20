<?php

namespace Morebec\Orkestra\Messaging\Normalization;

/**
 * In order to persist messages (for example events in an event store, such as a command store or scheduled messages etc.
 * and retain typing information without requiring to save FQDNs (Which is complex to change whenever namespaces change for types)
 * a map of message type names and their actual types needs to be created for deserialization purposes.
 * This interface provides the contract for such functionality.
 * It is used by the {@link ClassMapMessageNormalizer}.
 */
interface MessageClassMapInterface
{
    /**
     * Adds a new mapping between a Message with a given type name and its corresponding class name.
     */
    public function addMapping(string $messageTypeName, string $messageClassName): void;

    /**
     * Returns the class name associated to a Message type.
     * If no mapping exists, returns null.
     */
    public function getClassNameForMessageTypeName(string $messageTypeName): ?string;

    /**
     * Returns an array representation of this class map, where keys are Message type
     * names and values their associated class name.
     */
    public function toArray(): array;
}
