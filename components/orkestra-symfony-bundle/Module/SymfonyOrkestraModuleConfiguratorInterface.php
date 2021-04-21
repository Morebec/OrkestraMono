<?php

namespace Morebec\Orkestra\SymfonyBundle\Module;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Represents a Symfony based Orkestra Module.
 */
interface SymfonyOrkestraModuleConfiguratorInterface
{
    /**
     * Configures the Symfony service container.
     */
    public function configureContainer(ContainerConfigurator $container): void;

    /**
     * Configures the Symfony route to be used by symfony for this module.
     */
    public function configureRoutes(RoutingConfigurator $routes): void;
}
