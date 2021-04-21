<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery;

use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\BlacklistService\BlacklistServiceCommandHandler;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\RegisterService\RegisterServiceCommandHandler;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\ServiceRegistryInterface;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\UnregisterService\UnregisterServiceCommandHandler;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Infrastructure\EventStoreServiceRegistry;
use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleConfiguratorInterface;
use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class ServiceDiscoveryModuleConfigurator implements SymfonyOrkestraModuleConfiguratorInterface
{
    public function configureContainer(ContainerConfigurator $container): void
    {
        $config = new SymfonyOrkestraModuleContainerConfigurator($container);

        $config->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure()
        ;

        $config->commandHandler(RegisterServiceCommandHandler::class)->autoroute();
        $config->commandHandler(UnregisterServiceCommandHandler::class)->autoroute();
        $config->commandHandler(BlacklistServiceCommandHandler::class)->autoroute();

        $config->repository(ServiceRegistryInterface::class, EventStoreServiceRegistry::class);
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
