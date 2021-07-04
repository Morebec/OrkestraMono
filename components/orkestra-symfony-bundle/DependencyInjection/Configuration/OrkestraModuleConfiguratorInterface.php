<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Represents the configuration of a module's dependencies.
 */
interface OrkestraModuleConfiguratorInterface
{
    /**
     * Configures the Symfony service container.
     */
    public function configureContainer(OrkestraConfiguration $configuration): void;

    /**
     * Configures the Symfony route to be used by symfony for this module.
     */
    public function configureRoutes(RoutingConfigurator $routes): void;
}
