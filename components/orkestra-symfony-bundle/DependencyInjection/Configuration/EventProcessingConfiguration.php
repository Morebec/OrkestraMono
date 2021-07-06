<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\EventSourcing\EventProcessor\InMemoryEventStorePositionStorage;

class EventProcessingConfiguration
{
    public array $eventStorePositionStorageImplementationClassNames = [];
    public ?ProjectionProcessingConfiguration $projectionProcessingConfiguration = null;

    public function usingEventStorePositionStorageImplementation(string $className): self
    {
        $this->eventStorePositionStorageImplementationClassNames[] = $className;

        return $this;
    }

    public function usingInMemoryEventStorePositionStorage(): self
    {
        return $this->usingEventStorePositionStorageImplementation(InMemoryEventStorePositionStorage::class);
    }

    public function configureProjectionProcessing(ProjectionProcessingConfiguration $configuration): self
    {
        $this->projectionProcessingConfiguration = $configuration;

        return $this;
    }
}
