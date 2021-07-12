<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\NotConfiguredException;

class MessagingConfiguration
{
    /** @var MessageBusConfiguration[] */
    public array $messageBuses = [];

    public ?TimeoutProcessingConfiguration $timeoutProcessingConfiguration = null;

    public ?MessageNormalizerConfiguration $messageNormalizerConfiguration = null;

    /**
     * Configures a message bus.
     *
     * @return $this
     */
    public function configureMessageBus(MessageBusConfiguration $configuration): self
    {
        $this->messageBuses[$configuration->serviceId] = $configuration;

        return $this;
    }

    /**
     * Returns a previously configured message bus.
     */
    public function messageBus(string $serviceId): MessageBusConfiguration
    {
        $messageBus = $this->messageBuses[$serviceId] ?? null;
        if (!$messageBus) {
            throw new NotConfiguredException("Message bus with serviceId \"$serviceId\" was not configured.");
        }

        return $messageBus;
    }

    /**
     * Configures timeout processing.
     *
     * @return $this
     */
    public function configureTimeoutProcessing(TimeoutProcessingConfiguration $configuration): self
    {
        $this->timeoutProcessingConfiguration = $configuration;

        return $this;
    }

    /**
     * Returns the previously configured timeout processing configuration, or throws an exception if it was not configured.
     */
    public function timeoutProcessing(): TimeoutProcessingConfiguration
    {
        if (!$this->timeoutProcessingConfiguration) {
            throw new NotConfiguredException('Timeout Processing not configured.');
        }

        return $this->timeoutProcessingConfiguration;
    }

    /**
     * Configures the normalization of messages.
     *
     * @return $this
     */
    public function configureMessageNormalizer(MessageNormalizerConfiguration $configuration): self
    {
        $this->messageNormalizerConfiguration = $configuration;

        return $this;
    }

    /**
     * Returns the previously configured message normalizer configuration, or throws an exception if it was not configured.
     */
    public function messageNormalizer(): MessageNormalizerConfiguration
    {
        if (!$this->messageNormalizerConfiguration) {
            throw new NotConfiguredException('Message Normalizer was not configured.');
        }

        return $this->messageNormalizerConfiguration;
    }
}
