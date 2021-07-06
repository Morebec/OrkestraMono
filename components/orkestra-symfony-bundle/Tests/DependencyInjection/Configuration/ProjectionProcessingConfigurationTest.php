<?php

namespace Tests\Morebec\OrkestraSymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\ProjectionProcessingConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\ProjectorGroupConfiguration;
use PHPUnit\Framework\TestCase;

class ProjectionProcessingConfigurationTest extends TestCase
{
    public function testGetProjectorGroupConfiguration(): void
    {
        $configuration = new ProjectionProcessingConfiguration();
        $projectorGroupConfiguration = (new ProjectorGroupConfiguration())->withName('test');
        $configuration->configureProjectorGroup(($projectorGroupConfiguration));

        self::assertEquals($projectorGroupConfiguration, $configuration->getProjectorGroupConfiguration('test'));
    }

    public function testConfigureProjectorGroup(): void
    {
        $configuration = new ProjectionProcessingConfiguration();
        $projectorGroupConfiguration = (new ProjectorGroupConfiguration())->withName('test');
        $configuration->configureProjectorGroup(($projectorGroupConfiguration));
        self::assertEquals($projectorGroupConfiguration, $configuration->projectorGroups['test']);
    }
}
