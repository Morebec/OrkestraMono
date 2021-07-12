<?php

namespace Morebec\Orkestra\SymfonyBundle\Tests\DependencyInjection\Configuration\Messaging;

use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging\MessageBusConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging\MessageNormalizerConfiguration;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging\MessagingConfiguration;
use PHPUnit\Framework\TestCase;

class MessagingConfigurationTest extends TestCase
{
    public function testConfigureMessageBus(): void
    {
        $configuration = new MessagingConfiguration();

        $messageBusConfiguration = new MessageBusConfiguration();
        $messageBusConfiguration->usingServiceId(MessageBusInterface::class);

        $configuration->configureMessageBus($messageBusConfiguration);

        self::assertContains($messageBusConfiguration, $configuration->messageBuses);

        self::assertEquals($configuration->messageBus(MessageBusInterface::class), $messageBusConfiguration);
    }

    public function testConfigureMessageNormalizer(): void
    {
        $configuration = new MessagingConfiguration();

        $messageNormalizerConfiguration = new MessageNormalizerConfiguration();
        $messageNormalizerConfiguration->usingDefaultImplementation();

        $configuration->configureMessageNormalizer($messageNormalizerConfiguration);

        self::assertEquals($messageNormalizerConfiguration, $configuration->messageNormalizerConfiguration);
    }
}
