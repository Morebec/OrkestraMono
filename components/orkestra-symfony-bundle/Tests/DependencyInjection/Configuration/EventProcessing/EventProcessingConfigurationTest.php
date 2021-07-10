<?php

namespace Tests\Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing;

use Morebec\Orkestra\EventSourcing\EventProcessor\InMemoryEventStorePositionStorage;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing\EventProcessingConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing\ProjectionProcessingConfiguration;
use PHPUnit\Framework\TestCase;

class EventProcessingConfigurationTest extends TestCase
{
    public function testUsingEventStorePositionStorageImplementation(): void
    {
        $configuration = new EventProcessingConfiguration();
        $configuration->usingEventStorePositionStorageImplementation(InMemoryEventStorePositionStorage::class);

        self::assertContains(InMemoryEventStorePositionStorage::class, $configuration->eventStorePositionStorageImplementationClassNames);
    }

    public function testUsingInMemoryEventStorePositionStorage(): void
    {
        $configuration = new EventProcessingConfiguration();
        $configuration->usingInMemoryEventStorePositionStorage();

        self::assertContains(InMemoryEventStorePositionStorage::class, $configuration->eventStorePositionStorageImplementationClassNames);
    }

    public function testConfigureProjectionProcessing(): void
    {
        $configuration = new EventProcessingConfiguration();

        $projectionConfiguration = new ProjectionProcessingConfiguration();
        $configuration->configureProjectionProcessing($projectionConfiguration);

        self::assertEquals($projectionConfiguration, $configuration->projectionProcessingConfiguration);
    }
}
