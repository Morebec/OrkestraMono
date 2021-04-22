<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth;

use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\DisableHealthChecking\DisableHealthCheckingWhenServiceUnregisteredEvent;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\DisableHealthChecking\DisableServiceHealthCheckingCommandHandler;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\EnableHealthChecking\EnableHealthCheckingWhenServiceRegisteredEventHandler;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\EnableHealthChecking\EnableServiceHealthCheckingCommandHandler;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckRepositoryInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckRunnerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\RunHealthCheck\RunHealthCheckCommandHandler;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\UpdateServiceChecksCommandHandler;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheckThresholdCounterRepositoryInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceHealthChecker;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceRepositoryInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Infrastructure\EventStoreHealthCheckRepository;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Infrastructure\EventStoreServiceRepository;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Infrastructure\HttpHealthCheckRunner;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Infrastructure\PostgreSqlServiceCheckThresholdCounterRepository;
use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleConfiguratorInterface;
use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class ServiceHealthModuleConfigurator implements SymfonyOrkestraModuleConfiguratorInterface
{
    public function configureContainer(ContainerConfigurator $container): void
    {
        $config = new SymfonyOrkestraModuleContainerConfigurator($container);
        $config->defaults()
            ->autoconfigure()
            ->autowire()
        ;

        $config->repository(ServiceRepositoryInterface::class, EventStoreServiceRepository::class);
        $config->commandHandler(EnableServiceHealthCheckingCommandHandler::class)->autoroute();
        $config->commandHandler(DisableServiceHealthCheckingCommandHandler::class)->autoroute();
        $config->commandHandler(UpdateServiceChecksCommandHandler::class)->autoroute();

        $config->eventHandler(EnableHealthCheckingWhenServiceRegisteredEventHandler::class)->autoroute();
        $config->eventHandler(DisableHealthCheckingWhenServiceUnregisteredEvent::class)->autoroute();

        $config->processManager(ServiceHealthChecker::class)->autoroute();
        $config->repository(HealthCheckRepositoryInterface::class, EventStoreHealthCheckRepository::class);
        $config->commandHandler(RunHealthCheckCommandHandler::class)->autoroute();
        $config->service(HealthCheckRunnerInterface::class, HttpHealthCheckRunner::class);
        $config->repository(ServiceCheckThresholdCounterRepositoryInterface::class, PostgreSqlServiceCheckThresholdCounterRepository::class);
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
