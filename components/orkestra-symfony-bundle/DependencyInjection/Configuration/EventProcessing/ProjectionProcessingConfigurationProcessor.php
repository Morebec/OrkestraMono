<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing;

use Morebec\Orkestra\EventSourcing\Projection\ProjectorGroup;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\OrkestraConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\ProjectorGroupRegistry;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @internal
 */
class ProjectionProcessingConfigurationProcessor
{
    public function process(OrkestraConfiguration $orkestraConfiguration, ProjectionProcessingConfiguration $configuration): void
    {
        $registryService = $orkestraConfiguration->service(ProjectorGroupRegistry::class);

        // Register Projector groups and their projectors.
        /** @var ProjectorGroupConfiguration $projectorGroup */
        foreach ($configuration->projectorGroups as $projectorGroup) {
            $projectorGroupServiceId = ProjectorGroupConfiguration::DEFAULT_PREFIX.$projectorGroup->groupName;
            $projectorGroupService = $orkestraConfiguration
                ->service($projectorGroupServiceId, ProjectorGroup::class)
                    ->share(false)
                    ->lazy()
                    ->public()
                    ->args([$projectorGroup->groupName, []])
            ;

            // Projector Group to registry
            $registryService->call('addProjectorGroup', [service($projectorGroupServiceId)]);

            // Add Projectors to Projector Group
            foreach ($projectorGroup->projectors as $projectorClassName) {
                try {
                    $orkestraConfiguration->container()->services()->get($projectorClassName);
                } catch (ServiceNotFoundException $exception) {
                    $orkestraConfiguration->service($projectorClassName);
                }
                $projectorGroupService->call('addProjector', [service($projectorClassName)]);
            }
        }
    }
}
