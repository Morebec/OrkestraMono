<?php

namespace Tests\Morebec\OrkestraSymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\EventSourcing\EventProcessor\InMemoryEventStorePositionStorage;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessingConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\ProjectionProcessingConfiguration;
use PHPUnit\Framework\TestCase;

class EventProcessingConfigurationTest extends TestCase
{
    public function testUsingEventStorePositionStorageImplementation(): void
    {
        $configuration = new EventProcessingConfiguration();
        $configuration->usingEventStorePositionStorageImplementation(InMemoryEventStorePositionStorage::class);

        self::assertEquals(InMemoryEventStorePositionStorage::class, $configuration->eventStorePositionStorageImplementationClassName);
    }

    public function testUsingInMemoryEventStorePositionStorage(): void
    {
        $configuration = new EventProcessingConfiguration();
        $configuration->usingInMemoryEventStorePositionStorage();

        self::assertEquals(InMemoryEventStorePositionStorage::class, $configuration->eventStorePositionStorageImplementationClassName);
    }

    public function testConfigureProjectionProcessing(): void
    {
        $configuration = new EventProcessingConfiguration();

        $projectionConfiguration = new ProjectionProcessingConfiguration();
        $configuration->configureProjectionProcessing($projectionConfiguration);

        self::assertEquals($projectionConfiguration, $configuration->projectionProcessingConfiguration);
    }
}
