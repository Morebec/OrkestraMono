<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\Messaging\Timeout\TimeoutManagerInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutStorageInterface;

class TimeoutProcessingConfigurationProcessor
{
    public function process(
        OrkestraConfiguration $orkestraConfiguration,
        TimeoutProcessingConfiguration $configuration
    ): void {
        $orkestraConfiguration->service(TimeoutManagerInterface::class, $configuration->managerImplementationClassName);
        $orkestraConfiguration->service(TimeoutStorageInterface::class, $configuration->storageImplementationClassName);
    }
}
