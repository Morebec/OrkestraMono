<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\Messaging\Timeout\TimeoutManagerInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutStorageInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class TimeoutProcessingConfigurationProcessor
{
    public function process(
        OrkestraConfiguration $orkestraConfiguration,
        TimeoutProcessingConfiguration $configuration
    ): void {
        try {
            $orkestraConfiguration->container()->services()->get(TimeoutManagerInterface::class);
        } catch (ServiceNotFoundException $exception) {
            $orkestraConfiguration->service(TimeoutManagerInterface::class, $configuration->managerImplementationClassName);
        }

        try {
            $orkestraConfiguration->container()->services()->get(TimeoutStorageInterface::class);
        } catch (ServiceNotFoundException $exception) {
            $orkestraConfiguration->service(TimeoutStorageInterface::class, $configuration->storageImplementationClassName);
        }
    }
}
