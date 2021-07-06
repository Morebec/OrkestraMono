<?php

namespace Tests\Morebec\OrkestraSymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\Messaging\Timeout\InMemoryTimeoutStorage;
use Morebec\Orkestra\Messaging\Timeout\TimeoutManager;
use Morebec\Orkestra\Messaging\Timeout\TimeoutManagerInterface;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\TimeoutProcessingConfiguration;
use PHPUnit\Framework\TestCase;

class TimeoutProcessingConfigurationTest extends TestCase
{
    public function testUsingDefaultManagerImplementation(): void
    {
        $configuration = new TimeoutProcessingConfiguration();
        $configuration->usingDefaultManagerImplementation();

        self::assertEquals(TimeoutManager::class, $configuration->managerImplementationClassName);
    }

    public function testUsingManagerImplementation(): void
    {
        $configuration = new TimeoutProcessingConfiguration();
        $configuration->usingManagerImplementation(TimeoutManagerInterface::class);
        self::assertEquals(TimeoutManagerInterface::class, $configuration->managerImplementationClassName);
    }

    public function testUsingStorageImplementation(): void
    {
        $configuration = new TimeoutProcessingConfiguration();
        $configuration->usingStorageImplementation(InMemoryTimeoutStorage::class);

        self::assertEquals(InMemoryTimeoutStorage::class, $configuration->storageImplementationClassName);
    }
}
