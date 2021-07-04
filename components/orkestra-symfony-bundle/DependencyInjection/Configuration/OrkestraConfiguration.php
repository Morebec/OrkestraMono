<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

/**
 * Main configuration for the orkestra components in a Symfony setup.
 * It provides a friendly API to define dependencies related to Orkestra Components.
 */
class OrkestraConfiguration
{
    /** @var MessageBusConfiguration|null */
    private $messageBusConfiguration;

    /** @var EventStoreConfiguration|null */
    private $eventStoreConfiguration;

    /** @var TimeoutProcessingConfiguration|null */
    private $timeoutProcessingConfiguration;

    /** @var EventProcessingConfiguration|null */
    private $eventProcessingConfiguration;

    /** @var ContainerConfigurator */
    private $container;

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
}
