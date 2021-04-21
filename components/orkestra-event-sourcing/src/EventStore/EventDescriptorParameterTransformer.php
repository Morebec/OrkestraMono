<?php

namespace Morebec\Orkestra\EventSourcing\EventStore;

/** @internal  */
final class EventDescriptorParameterTransformer
{
    public static function stringOrEventType($type): EventType
    {
        if (\is_string($type)) {
            $type = EventType::fromString($type);
        }

        if (!($type instanceof EventType)) {
            throw new \InvalidArgumentException(sprintf('Event Type must be a string or "%s", got "%s".', EventType::class, get_debug_type($type)));
        }

        return $type;
    }

    public static function arrayOrEventData($data): EventData
    {
        if (\is_array($data)) {
            $data = new EventData($data);
        }

        if (!($data instanceof EventData)) {
            throw new \InvalidArgumentException(sprintf('Event Data must be an array or "%s", got "%s".', EventData::class, get_debug_type($data)));
        }

        return $data;
    }

    public static function arrayOrEventMetadata($metadata): EventMetadata
    {
        if (\is_array($metadata)) {
            $metadata = new EventMetadata($metadata);
        }

        if (!($metadata instanceof EventMetadata)) {
            throw new \InvalidArgumentException(sprintf('Event Metadata must be an array or "%s", got "%s".', EventMetadata::class, get_debug_type($metadata)));
        }

        return $metadata;
    }
}
