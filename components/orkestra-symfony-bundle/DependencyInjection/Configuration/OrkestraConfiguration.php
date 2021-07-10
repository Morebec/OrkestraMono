<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing\EventProcessingConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventStore\EventStoreConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging\MessagingConfiguration;
use Morebec\OrkestraSymfonyBundle\Tests\DependencyInjection\Configuration\NotConfiguredException;
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

    public ?MessagingConfiguration $messagingConfiguration = null;

    private ?EventStoreConfiguration $eventStoreConfiguration = null;

    private ?EventProcessingConfiguration $eventProcessingConfiguration = null;

    private ContainerConfigurator $container;

    private ServicesConfigurator $services;

    public function __construct(ContainerConfigurator $container)
    {
        $this->container = $container;
        $this->services = $container->services();
        $this->services
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
     * Allows configuring messaging.
     *
     * @return $this
     */
    public function configureMessaging(MessagingConfiguration $configuration): self
    {
        $this->messagingConfiguration = $configuration;

        return $this;
    }

    /**
     * Returns the messaging configuration or throws an exception if it was not configured.
     */
    public function messaging(): MessagingConfiguration
    {
        if (!$this->messagingConfiguration) {
            throw new NotConfiguredException('Messaging was not configured.');
        }

        return $this->messagingConfiguration;
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

    /**
     * Returns the previously configured event store configuration.
     */
    public function eventStore(): EventStoreConfiguration
    {
        if (!$this->eventProcessingConfiguration) {
            throw new NotConfiguredException('Event Store was not configured.');
        }

        return $this->eventStoreConfiguration;
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

    /**
     * Returns the previously configured event processing configuration or throws an exception if not found.
     */
    public function eventProcessing(): EventProcessingConfiguration
    {
        if (!$this->eventProcessingConfiguration) {
            throw new NotConfiguredException('Event Processing was not configured.');
        }

        return $this->eventProcessingConfiguration;
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
}
