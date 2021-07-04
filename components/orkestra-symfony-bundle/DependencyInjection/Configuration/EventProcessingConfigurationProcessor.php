<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\EventSourcing\EventProcessor\EventStorePositionStorageInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class EventProcessingConfigurationProcessor
{
    public function process(
        OrkestraConfiguration $orkestraConfiguration,
        EventProcessingConfiguration $configuration
    ): void {
        try {
            $orkestraConfiguration->container()->services()->get(EventStorePositionStorageInterface::class);
        } catch (ServiceNotFoundException $exception) {
            $orkestraConfiguration->service(
                EventStorePositionStorageInterface::class,
                $configuration->eventStorePositionStorageImplementationClassName
            );
        }

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
