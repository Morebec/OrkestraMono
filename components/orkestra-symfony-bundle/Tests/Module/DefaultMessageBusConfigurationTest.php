<?php

namespace Morebec\Orkestra\SymfonyBundle\Tests\Module;

use Morebec\Orkestra\Messaging\Authorization\MessageAuthorizerInterface;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerInterceptorInterface;
use Morebec\Orkestra\Messaging\Validation\MessageValidatorInterface;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\DefaultMessageBusConfiguration;
use PHPUnit\Framework\TestCase;

class DefaultMessageBusConfigurationTest extends TestCase
{
    public function testWithMessageValidator(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->withMessageValidator(MessageValidatorInterface::class)
        ;

        self::assertContains(MessageValidatorInterface::class, $configuration->validators);
    }

    public function testWithMessageAuthorizer(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->withMessageAuthorizer(MessageAuthorizerInterface::class)
        ;

        self::assertContains(MessageAuthorizerInterface::class, $configuration->authorizers);
    }

    public function testWithMessageHandlerInterceptor(): void
    {
        $configuration = (new DefaultMessageBusConfiguration())
            ->withMessageHandlerInterceptor(MessageHandlerInterceptorInterface::class)
        ;

        self::assertContains(MessageHandlerInterceptorInterface::class, $configuration->messageHandlerInterceptors);
    }
}
