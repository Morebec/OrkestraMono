<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * This configuration is used to return a Noop Orkestra Configuration
 * if the configured env does not correspond to the current environment.
 */
class NullOrkestraConfiguration extends OrkestraConfiguration
{
    public function __construct()
    {
        // Create an empty container Configurator.
        $instanceOf = [];
        $cb = new ContainerBuilder();
        $containerConfigurator = new ContainerConfigurator($cb, new PhpFileLoader($cb, new FileLocator([])), $instanceOf, '', '');
        parent::__construct($containerConfigurator);
    }
}
