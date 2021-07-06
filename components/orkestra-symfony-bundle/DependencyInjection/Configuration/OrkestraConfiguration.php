<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

/**
 * Main configuration for the orkestra components in a Symfony setup.
 * It provides a friendly API to define dependencies related to Orkestra Components.
 */
class OrkestraConfiguration
{
    /**
     * @var CompilerPassInterface[]
     */
    public array $compilerPasses = [];
    private ?MessageBusConfiguration $messageBusConfiguration;

    private ?EventStoreConfiguration $eventStoreConfiguration;

    private ?TimeoutProcessingConfiguration $timeoutProcessingConfiguration;

    private ?EventProcessingConfiguration $eventProcessingConfiguration;

    private ContainerConfigurator $container;

    /** @var ServicesConfigurator */
    private $services;

    public function __construct(ContainerConfigurator $container)
    {
        $this->container = $container;
        $this->services = $container->services()
            ->defaults()
                ->autoconfigure()
                ->autowire()
        ;
    }

    public function usingClock(string $className): self
    {
        $this->service(ClockInterface::class, $className);

        return $this;
    }

    public function usingSystemClock(): self
    {
        $this->usingClock(SystemClock::class);

        return $this;
    }

    /**
     * Allows configuring the message bus.
     *
     * @return $this
     */
    public function configureMessageBus(MessageBusConfiguration $c): self
    {
        $this->messageBusConfiguration = $c;

        return $this;
    }

    public function getMessageBusConfiguration(): ?MessageBusConfiguration
    {
        return $this->messageBusConfiguration;
    }

    /**
     * Allows configuring the event store.
     *
     * @return $this
     */
    public function configureEventStore(EventStoreConfiguration $c): self
    {
        $this->eventStoreConfiguration = $c;

        return $this;
    }

    public function getEventStoreConfiguration(): ?EventStoreConfiguration
    {
        return $this->eventStoreConfiguration;
    }

    /**
     * Configures Timeout Processing.
     *
     * @return $this
     */
    public function configureTimeoutProcessing(TimeoutProcessingConfiguration $configuration): self
    {
        $this->timeoutProcessingConfiguration = $configuration;

        return $this;
    }

    public function getTimeoutProcessingConfiguration(): ?TimeoutProcessingConfiguration
    {
        return $this->timeoutProcessingConfiguration;
    }

    /**
     * Configures Event Processing.
     *
     * @return $this
     */
    public function configureEventProcessing(EventProcessingConfiguration $configuration): self
    {
        $this->eventProcessingConfiguration = $configuration;

        return $this;
    }

    public function getEventProcessingConfiguration(): ?EventProcessingConfiguration
    {
        return $this->eventProcessingConfiguration;
    }

    public function getProjectionProcessingConfiguration(): ?ProjectionProcessingConfiguration
    {
        return $this->eventProcessingConfiguration->projectionProcessingConfiguration ?? null;
    }

    // SYMFONY SPECIFIC
    public function container(): ContainerConfigurator
    {
        return $this->container;
    }

    /**
     * Configures a console command.
     */
    public function consoleCommand(string $className): ServiceConfigurator
    {
        return $this->services
            ->set($className)
            ->tag('console.command')
            ;
    }

    /**
     * Configures a controller.
     */
    public function controller(string $serviceId, ?string $serviceClass = null): ServiceConfigurator
    {
        return $this->services
            ->set($serviceId, $serviceClass)
            ->tag('controller.service_arguments');
    }

    /**
     * Configures a generic service.
     */
    public function service(string $serviceId, ?string $serviceClass = null): ServiceConfigurator
    {
        return $this->services->set($serviceId, $serviceClass);
    }

    public function compilerPass(CompilerPassInterface $compilerPass): self
    {
        $this->compilerPasses[] = $compilerPass;

        return $this;
    }

    // ORKESTRA SPECIFIC

    /**
     * Configures a Command handler with the message bus.
     */
    public function commandHandler(string $serviceId, string $className = null, bool $autoroute = true): DefaultMessageBusConfiguration
    {
        if (!($this->messageBusConfiguration instanceof DefaultMessageBusConfiguration)) {
            throw new \RuntimeException('The commandHandler method can only be used with the DefaultMessageBusConfiguration');
        }

        return $this->messageBusConfiguration->commandHandler($serviceId, $className, $autoroute);
    }

    /**
     * Configures a Query handler with the message bus.
     */
    public function queryHandler(string $serviceId, string $className = null, bool $autoroute = true): DefaultMessageBusConfiguration
    {
        if (!($this->messageBusConfiguration instanceof DefaultMessageBusConfiguration)) {
            throw new \RuntimeException('The queryHandler method can only be used with the DefaultMessageBusConfiguration');
        }

        return $this->messageBusConfiguration->queryHandler($serviceId, $className, $autoroute);
    }

    /**
     * Configures an Event handler with the message bus.
     */
    public function eventHandler(string $serviceId, string $className = null, bool $autoroute = true): DefaultMessageBusConfiguration
    {
        if (!($this->messageBusConfiguration instanceof DefaultMessageBusConfiguration)) {
            throw new \RuntimeException('The eventHandler method can only be used with the DefaultMessageBusConfiguration');
        }

        return $this->messageBusConfiguration->eventHandler($serviceId, $className, $autoroute);
    }

    /**
     * Configures a timeout handler with the message bus.
     */
    public function timeoutHandler(string $serviceId, string $className = null, bool $autoroute = true): DefaultMessageBusConfiguration
    {
        if (!($this->messageBusConfiguration instanceof DefaultMessageBusConfiguration)) {
            throw new \RuntimeException('The timeoutHandler method can only be used with the DefaultMessageBusConfiguration');
        }

        return $this->messageBusConfiguration->timeoutHandler($serviceId, $className, $autoroute);
    }

    /**
     * Configures a process manager with the message bus.
     */
    public function processManager(string $serviceId, string $className = null, bool $autoroute = true): DefaultMessageBusConfiguration
    {
        if (!($this->messageBusConfiguration instanceof DefaultMessageBusConfiguration)) {
            throw new \RuntimeException('The processManager method can only be used with the DefaultMessageBusConfiguration');
        }

        return $this->messageBusConfiguration->processManager($serviceId, $className, $autoroute);
    }

    /**
     * Configures a message handler with the message bus.
     */
    public function messageHandler(string $serviceId, ?string $className, bool $autoroute): DefaultMessageBusConfiguration
    {
        if (!($this->messageBusConfiguration instanceof DefaultMessageBusConfiguration)) {
            throw new \RuntimeException('The messageHandler method can only be used with the DefaultMessageBusConfiguration');
        }

        return $this->messageBusConfiguration->messageHandler($serviceId, $className, $autoroute);
    }
}
