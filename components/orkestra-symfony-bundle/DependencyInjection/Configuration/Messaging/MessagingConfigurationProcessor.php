<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

use Morebec\Orkestra\Messaging\Normalization\ClassMapMessageNormalizer;
use Morebec\Orkestra\Messaging\Normalization\MessageClassMapInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\OrkestraConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\SymfonyMessageClassMapFactory;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @internal
 */
class MessagingConfigurationProcessor
{
    private MessageBusConfigurationProcessor $messageBusConfigurationProcessor;

    private TimeoutProcessingConfigurationProcessor $timeoutProcessingConfigurationProcessor;

    public function __construct()
    {
        $this->messageBusConfigurationProcessor = new MessageBusConfigurationProcessor();
        $this->timeoutProcessingConfigurationProcessor = new TimeoutProcessingConfigurationProcessor();
    }

    public function process(OrkestraConfiguration $orkestraConfiguration, MessagingConfiguration $configuration): void
    {
        // Message Buses
        foreach ($configuration->messageBuses as $messageBusConfiguration) {
            $this->processMessageBus($orkestraConfiguration, $messageBusConfiguration);
        }

        // Timeout Processing.
        if ($configuration->timeoutProcessingConfiguration !== null) {
            $this->processTimeoutProcessing($orkestraConfiguration, $configuration);
        }

        // Message Normalizer
        $this->processMessageNormalizer($orkestraConfiguration, $configuration);
    }

    protected function processMessageNormalizer(
        OrkestraConfiguration $orkestraConfiguration,
        MessagingConfiguration $messagingConfiguration
    ): void {
        if (!$messagingConfiguration->messageNormalizerConfiguration) {
            $messagingConfiguration->configureMessageNormalizer(
                (new MessageNormalizerConfiguration())
                    ->usingDefaultImplementation()
            );
        }

        $messageNormalizerConfiguration = $messagingConfiguration->messageNormalizerConfiguration;

        try {
            $messageNormalizerService = $orkestraConfiguration->container()->services()->get(MessageNormalizerInterface::class);
        } catch (ServiceNotFoundException $exception) {
            $messageNormalizerService = $orkestraConfiguration->service(
                MessageNormalizerInterface::class,
                $messageNormalizerConfiguration->implementationClassName
            );

            if ($messageNormalizerConfiguration->implementationClassName === ClassMapMessageNormalizer::class) {
                $orkestraConfiguration->service(SymfonyMessageClassMapFactory::class);
                $orkestraConfiguration->service(MessageClassMapInterface::class)
                    ->factory([service(SymfonyMessageClassMapFactory::class), 'buildClassMap']);
            }
        }

        // Add normalizers
        foreach ($messageNormalizerConfiguration->normalizers as $normalizerClassName) {
            try {
                $orkestraConfiguration->container()->services()->get($normalizerClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($normalizerClassName);
                $messageNormalizerService->call('addNormalizer', [service($normalizerClassName)]);
            }
        }

        // Add denormalizers
        foreach ($messageNormalizerConfiguration->denormalizers as $denormalizerClassName) {
            try {
                $orkestraConfiguration->container()->services()->get($denormalizerClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($denormalizerClassName);
                $messageNormalizerService->call('addDenormalizer', [service($denormalizerClassName)]);
            }
        }
    }

    protected function processMessageBus(OrkestraConfiguration $orkestraConfiguration, MessageBusConfiguration $messageBusConfiguration): void
    {
        $this->messageBusConfigurationProcessor->process($orkestraConfiguration, $messageBusConfiguration);
    }

    protected function processTimeoutProcessing(OrkestraConfiguration $orkestraConfiguration, MessagingConfiguration $configuration): void
    {
        $this->timeoutProcessingConfigurationProcessor->process(
            $orkestraConfiguration,
            $configuration->timeoutProcessingConfiguration
        );
    }
}
