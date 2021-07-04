<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class AbstractOrkestraModuleConfigurator implements OrkestraModuleConfiguratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function configureContainer(OrkestraConfiguration $configuration): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
