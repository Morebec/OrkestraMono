<?php

namespace Morebec\Orkestra\OrkestraServer\Core\DependencyInjection;

use Doctrine\DBAL\Connection;
use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\EventProcessor\EventStorePositionStorageInterface;
use Morebec\Orkestra\EventSourcing\EventProcessor\MessageBusEventPublisher;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\EventSourcing\EventStore\MessageBusContextEventStoreDecorator;
use Morebec\Orkestra\EventSourcing\EventStore\UpcastingEventStoreDecorator;
use Morebec\Orkestra\Messaging\Timeout\MessageBusTimeoutPublisher;
use Morebec\Orkestra\Messaging\Timeout\TimeoutManager;
use Morebec\Orkestra\Messaging\Timeout\TimeoutManagerInterface;
use Morebec\Orkestra\Messaging\Timeout\TimeoutStorageInterface;
use Morebec\Orkestra\OrkestraServer\Command\MainEventProcessorConsoleCommand;
use Morebec\Orkestra\OrkestraServer\Command\MainProjectionEventProcessorConsoleCommand;
use Morebec\Orkestra\OrkestraServer\Command\MainTimeoutProcessorConsoleCommand;
use Morebec\Orkestra\PostgreSqlDocumentStore\PostgreSqlDocumentStore;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStore;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStoreConfiguration;
use Morebec\Orkestra\PostgreSqlEventStore\PostgreSqlEventStorePositionStorage;
use Morebec\Orkestra\PostgreSqlTimeoutStorage\PostgreSqlTimeoutStorage;
use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleConfiguratorInterface;
use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class CoreModuleConfigurator implements SymfonyOrkestraModuleConfiguratorInterface
{
    public function configureContainer(ContainerConfigurator $container): void
    {
        $config = new SymfonyOrkestraModuleContainerConfigurator($container);
        $config->services()
                ->defaults()
                    ->autoconfigure()
                    ->autowire()
        ;

        $config->service(ClockInterface::class, SystemClock::class);

        $config->service(Connection::class)->factory([ConnectionFactory::class, 'create']);

        // Event Store
        $config->service(PostgreSqlEventStoreConfigurationFactory::class);
        $config->service(PostgreSqlEventStore::class);
        $config->service(PostgreSqlEventStoreConfiguration::class)
            ->factory([PostgreSqlEventStoreConfigurationFactory::class, 'create'])
        ;

        $config->service(UpcastingEventStoreDecorator::class)
            ->decorate(EventStoreInterface::class, null, 1)
            ->args([service('.inner')]);

        $config->service(EventStoreInterface::class, PostgreSqlEventStore::class);
        $config->service(MessageBusContextEventStoreDecorator::class)
            ->decorate(EventStoreInterface::class, null, 0)
            ->args([service('.inner')]);

        $config->service(PostgreSqlEventStorePositionStorageFactory::class);
        $config->service(EventStorePositionStorageInterface::class, PostgreSqlEventStorePositionStorage::class)
            ->factory([service(PostgreSqlEventStorePositionStorageFactory::class), 'create'])
        ;

        // Event Processing
        $config->service(MessageBusEventPublisher::class);
        $config->consoleCommand(MainEventProcessorConsoleCommand::class);

        // Timeouts and Timeout Processing
        $config->service(TimeoutManagerInterface::class, TimeoutManager::class);
        $config->service(PostgreSqlTimeoutStorageFactory::class);
        $config->service(TimeoutStorageInterface::class, PostgreSqlTimeoutStorage::class)
                ->factory([service(PostgreSqlTimeoutStorageFactory::class), 'create']);

        // General Storage
        $config->service(PostgreSqlDocumentStoreFactory::class);
        $config->service(PostgreSqlDocumentStore::class)->factory(
            [service(PostgreSqlDocumentStoreFactory::class), 'create']
        )
        ;

        $config->service(MessageBusTimeoutPublisher::class);
        $config->consoleCommand(MainTimeoutProcessorConsoleCommand::class);
        $config->consoleCommand(MainProjectionEventProcessorConsoleCommand::class);
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
