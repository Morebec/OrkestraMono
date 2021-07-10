<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing;

use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\OrkestraConfiguration;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @internal
 */
class EventProcessingConfigurationProcessor
{
    public function process(
        OrkestraConfiguration $orkestraConfiguration,
        EventProcessingConfiguration $configuration
    ): void {
        array_map(static function ($className) use ($orkestraConfiguration) {
            try {
                $orkestraConfiguration->container()->services()->get($className);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($className);
            }
        }, $configuration->eventStorePositionStorageImplementationClassNames);

        if ($configuration->projectionProcessingConfiguration) {
            $this->processProjectionProcessingConfiguration($orkestraConfiguration, $configuration->projectionProcessingConfiguration);
        }
    }

    protected function processProjectionProcessingConfiguration(
        OrkestraConfiguration $configuration,
        ProjectionProcessingConfiguration $projectionProcessingConfiguration
    ): void {
        $processor = new ProjectionProcessingConfigurationProcessor();
        $processor->process($configuration, $projectionProcessingConfiguration);
    }
}
