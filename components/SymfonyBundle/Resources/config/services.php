<?php

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\EventSourcing\Upcasting\UpcasterChain;
use Morebec\Orkestra\Messaging\Authorization\AuthorizationDecisionMakerInterface;
use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\Authorization\VetoAuthorizationDecisionMaker;
use Morebec\Orkestra\Messaging\Context\BuildMessageBusContextMiddleware;
use Morebec\Orkestra\Messaging\Context\MessageBusContextManager;
use Morebec\Orkestra\Messaging\Context\MessageBusContextManagerInterface;
use Morebec\Orkestra\Messaging\Context\MessageBusContextProvider;
use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\Middleware\LoggerMiddleware;
use Morebec\Orkestra\Messaging\Normalization\ClassMapMessageNormalizer;
use Morebec\Orkestra\Messaging\Normalization\MessageClassMapInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Messaging\Routing\ContainerMessageHandlerProvider;
use Morebec\Orkestra\Messaging\Routing\HandleMessageMiddleware;
use Morebec\Orkestra\Messaging\Routing\LoggingMessageHandlerInterceptor;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerProviderInterface;
use Morebec\Orkestra\Messaging\Routing\MessageRouterInterface;
use Morebec\Orkestra\Messaging\Routing\RouteMessageMiddleware;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;
use Morebec\Orkestra\SymfonyBundle\Command\DebugMessageClassMap;
use Morebec\Orkestra\SymfonyBundle\Command\DebugMessageRouter;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\SymfonyMessageClassMapFactory;
use Morebec\Orkestra\SymfonyBundle\Messaging\MessageRouterCache;
use Morebec\Orkestra\SymfonyBundle\Module\SymfonyOrkestraModuleContainerConfigurator;
use Morebec\OrkestraSymfonyBundle\Messaging\CachedMessageRouter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->private()
    ;

    // GENERAL SERVICES
    $services->set(ClockInterface::class, SystemClock::class);
    $services->set(ObjectNormalizerInterface::class, ObjectNormalizer::class);

    // MESSAGING
    $services->set(MessageNormalizerInterface::class, ClassMapMessageNormalizer::class);
    $services->set(SymfonyMessageClassMapFactory::class);
    $services->set(MessageClassMapInterface::class)->factory([service(SymfonyMessageClassMapFactory::class), 'buildClassMap']);

    // Message bus MIDDLEWARE
    // Message bus context.
    $services->set(BuildMessageBusContextMiddleware::class);
    $services->set(MessageBusContextManagerInterface::class, MessageBusContextManager::class);
    $services->set(MessageBusContextProvider::class, MessageBusContextProvider::class);

    // Logging middleware.
    $services->set(LoggerMiddleware::class)->tag('monolog.logger', ['channel' => 'message_bus']);

    // Validation.
    // Add services tagged with message validator.
    $services->set(ValidateMessageMiddleware::class)
        ->args([tagged_iterator(SymfonyOrkestraModuleContainerConfigurator::MESSAGE_VALIDATOR)])
    ;
    $services->set(ValidateMessageMiddleware::class);

    // Authorization.
    // Add services tagged with message authorizer.
    $services->set(AuthorizationDecisionMakerInterface::class, VetoAuthorizationDecisionMaker::class)
        ->args([tagged_iterator(SymfonyOrkestraModuleContainerConfigurator::MESSAGE_AUTHORIZER)])
    ;
    $services->set(AuthorizeMessageMiddleware::class);

    // Message Routing.
    $services->set(MessageRouterCache::class)->args(['%kernel.cache_dir%']);
    $services->set(MessageRouterInterface::class, CachedMessageRouter::class);
    $services->set(RouteMessageMiddleware::class);

    // Message handling.
    $services->set(MessageHandlerProviderInterface::class, ContainerMessageHandlerProvider::class);

    // Add services tagged with message handler interceptor.
    $services->set(HandleMessageMiddleware::class)->args([
        service(MessageHandlerProviderInterface::class),
        tagged_iterator(SymfonyOrkestraModuleContainerConfigurator::MESSAGE_HANDLER_INTERCEPTORS),
    ]);
    $services->set(LoggingMessageHandlerInterceptor::class)->tag('monolog.logger', ['channel' => 'message_bus']);

    // Setup message bus with middleware.
    // If one needs to have custom middleware
    // they can do:
    // $services->get(MessageBusInterface::class)->args([/* custom middleware */])
    // $services->get(MessageBusInterface::class)->call('appendMiddleware', [/* custom middleware */])
    // $services->get(MessageBusInterface::class)->call('prependMiddleware', [/* custom middleware */])
    // $services->get(MessageBusInterface::class)->call('replaceMiddleware', [/* custom middleware */])
    $services->set(MessageBusInterface::class, MessageBus::class)->args([
        [
            service(BuildMessageBusContextMiddleware::class),
            service(LoggerMiddleware::class),
            service(ValidateMessageMiddleware::class),
            service(AuthorizeMessageMiddleware::class),
            service(RouteMessageMiddleware::class),
            service(HandleMessageMiddleware::class),
        ],
    ]);

    // Upcasting
    // add services tagged with upcaster_tag
    $services->set(UpcasterChain::class)->args([tagged_iterator(SymfonyOrkestraModuleContainerConfigurator::UPCASTER_TAG)]);

    // Event Sourcing
    // Default to in memory implementation and add MessageBusContextEventStoreDecorator and UpcastingEventStoreDecorator
    // as top level decorators.
    // if one needs to add other decorators, we can simply do the same as this.
//        $services->set(MessageBusContextEventStoreDecorator::class)
//            ->decorate(EventStoreInterface::class, null, 0, ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
//            // ->args([service('.inner')])
//        ;
//
//        $services->set(UpcastingEventStoreDecorator::class)
//            ->decorate(EventStoreInterface::class, null, 1, ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
//            // ->args([service('.inner')])
//        ;

    // Console commands.
    $services->set(DebugMessageRouter::class)->tag('console.command');
    $services->set(DebugMessageClassMap::class)->tag('console.command');
};
