<?php

namespace Morebec\Orkestra\EventSourcing\Upcasting;

/**
 * Abstract implementation of an Event Specific Upcaster that also checks for the version of the event
 * in its `supports` method. by checking for a field `schemaVersion` in the metadata of the event.
 */
abstract class AbstractEventVersionSpecificUpcaster extends AbstractEventSpecificUpcaster
{
    /**
     * @var int
     */
    protected $schemaVersion;

    public function __construct(string $eventType, int $schemaVersion)
    {
        parent::__construct($eventType);
        $this->schemaVersion = $schemaVersion;
    }

    public function supports(UpcastableEventDescriptor $eventDescriptor): bool
    {
        if (!parent::supports($eventDescriptor)) {
            return false;
        }

        return $this->schemaVersion === $eventDescriptor->getMetadataKey('schemaVersion');
    }
}
