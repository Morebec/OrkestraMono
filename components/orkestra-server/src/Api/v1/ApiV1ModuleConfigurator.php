<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1;

use Morebec\Orkestra\OrkestraServer\Api\v1\Service\RegisterServiceController;
use Morebec\Orkestra\OrkestraServer\Api\v1\Service\ServiceViewProjector;
use Morebec\Orkestra\OrkestraServer\Api\v1\ServiceCheck\HealthCheckViewProjector;
use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleConfiguratorInterface;
use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class ApiV1ModuleConfigurator implements SymfonyOrkestraModuleConfiguratorInterface
{
    public function configureContainer(ContainerConfigurator $container): void
    {
        $config = new SymfonyOrkestraModuleContainerConfigurator($container);

        $config->services()
            ->defaults()
            ->autoconfigure()
            ->autowire()
        ;

        $config->service(ApiExceptionListener::class);
        $config->service(ApiRequestListener::class);

        $config->controller(CommandController::class);
        $config->controller(QueryController::class);
        $config->controller(RegisterServiceController::class);

        // Projections.
        $config->service(HealthCheckViewProjector::class);
        $config->service(ServiceViewProjector::class);
        $config->service(PostgreSqlProjectorGroup::class)->args([
            [
                service(ServiceViewProjector::class),
                service(HealthCheckViewProjector::class),
            ],
        ]);
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__, 'annotation')
            ->namePrefix('api.v1.')
            ->prefix('/api/v1')
        ;
    }
}
