<?php

namespace Tests\Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing;

use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing\ProjectionProcessingConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing\ProjectorGroupConfiguration;
use PHPUnit\Framework\TestCase;

class ProjectionProcessingConfigurationTest extends TestCase
{
    public function testGetProjectorGroupConfiguration(): void
    {
        $configuration = new ProjectionProcessingConfiguration();
        $projectorGroupConfiguration = (new ProjectorGroupConfiguration())->withName('test');
        $configuration->configureProjectorGroup(($projectorGroupConfiguration));

        self::assertEquals($projectorGroupConfiguration, $configuration->projectorGroup('test'));
    }

    public function testConfigureProjectorGroup(): void
    {
        $configuration = new ProjectionProcessingConfiguration();
        $projectorGroupConfiguration = (new ProjectorGroupConfiguration())->withName('test');
        $configuration->configureProjectorGroup(($projectorGroupConfiguration));
        self::assertEquals($projectorGroupConfiguration, $configuration->projectorGroups['test']);
    }
}
