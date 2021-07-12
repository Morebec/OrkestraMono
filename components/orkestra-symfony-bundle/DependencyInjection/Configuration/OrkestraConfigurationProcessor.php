<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\EventSourcing\EventStore\EventStoreInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventProcessing\EventProcessingConfigurationProcessor;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\EventStore\EventStoreConfigurationProcessor;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging\MessagingConfigurationProcessor;

class OrkestraConfigurationProcessor
{
    public function __construct()
    {
    }

    public function processConfiguration(OrkestraConfiguration $configuration): void
    {
        // CONFIGURE MESSAGING
        if (interface_exists(MessageBusInterface::class)) {
            $this->processMessagingConfiguration($configuration);
        }

        // CONFIGURE EVENT STORE
        if (interface_exists(EventStoreInterface::class)) {
            $this->processEventStoreConfiguration($configuration);
            $this->processEventProcessingConfiguration($configuration);
        }
    }

    public function processMessagingConfiguration(OrkestraConfiguration $configuration): void
    {
        $processor = new MessagingConfigurationProcessor();
        if ($configuration->messagingConfiguration) {
            $processor->process($configuration, $configuration->messagingConfiguration);
        }
    }

    /**
     * Configures the event store with the container configurator.
     */
    public function processEventStoreConfiguration(OrkestraConfiguration $configuration): void
    {
        $processor = new EventStoreConfigurationProcessor();

        try {
            $eventStore = $configuration->eventStore();
            $processor->process($configuration, $eventStore);
        } catch (NotConfiguredException $exception) {
        }
    }

    /**
     * Configures the event processing with the container configurator.
     */
    public function processEventProcessingConfiguration(OrkestraConfiguration $configuration): void
    {
        $processor = new EventProcessingConfigurationProcessor();

        try {
            $eventProcessingConfiguration = $configuration->eventProcessing();
            $processor->process($configuration, $eventProcessingConfiguration);
        } catch (NotConfiguredException $exception) {
        }
    }
}
