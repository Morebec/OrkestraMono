<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use JsonException;
use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\SymfonyBundle\OrkestraKernel;
use ReflectionException;
use RuntimeException;

class OrkestraConfigurationProcessor
{
    /** @var OrkestraKernel */
    private $kernel;

    public function __construct(OrkestraKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function processConfiguration(OrkestraConfiguration $configuration): void
    {
        // CONFIGURE MESSAGE BUS
        if (interface_exists(MessageBusInterface::class)) {
            $this->processMessageBusConfiguration($configuration);

            // CONFIGURE TIMEOUT PROCESSING
            $this->processTimeoutProcessingConfiguration($configuration);
        }

        // CONFIGURE EVENT STORE
        if (interface_exists(EventStoreInterface::class)) {
            $this->processEventStoreConfiguration($configuration);
            $this->processEventProcessingConfiguration($configuration);
        }
    }

    /**
     * Configures the message bus with the container configurator.
     *
     * @throws JsonException
     * @throws ReflectionException
     */
    public function processMessageBusConfiguration(OrkestraConfiguration $configuration): void
    {
        $messageBusConfiguration = $configuration->getMessageBusConfiguration();
        if (!$messageBusConfiguration) {
            throw new RuntimeException('The configuration of the Message Bus was not defined. Please define it explicitly');
        }

        $processor = new MessageBusConfigurationProcessor($this->kernel);
        $processor->process($configuration, $messageBusConfiguration);
    }

    public function processTimeoutProcessingConfiguration(OrkestraConfiguration $configuration): void
    {
        $processor = new TimeoutProcessingConfigurationProcessor();

        $timeoutProcessingConfiguration = $configuration->getTimeoutProcessingConfiguration();
        if ($timeoutProcessingConfiguration) {
            $processor->process($configuration, $timeoutProcessingConfiguration);
        }
    }

    /**
     * Configures the event store with the container configurator.
     */
    public function processEventStoreConfiguration(OrkestraConfiguration $configuration): void
    {
        $processor = new EventStoreConfigurationProcessor();
        $eventStoreConfiguration = $configuration->getEventStoreConfiguration();
        if ($eventStoreConfiguration) {
            $processor->process($configuration, $eventStoreConfiguration);
        }
    }

    public function processEventProcessingConfiguration(OrkestraConfiguration $configuration): void
    {
        $processor = new EventProcessingConfigurationProcessor();
        $eventProcessingConfiguration = $configuration->getEventProcessingConfiguration();

        if ($eventProcessingConfiguration) {
            $processor->process($configuration, $eventProcessingConfiguration);
        }
    }
}
