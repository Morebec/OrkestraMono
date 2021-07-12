<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\OrkestraConfiguration;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;

/**
 * Translates the {@link MessageBusConfiguration} to symfony container configuration calls.
 *
 * @internal
 */
class MessageBusConfigurationProcessor
{
    public function process(OrkestraConfiguration $orkestraConfiguration, MessageBusConfiguration $messageBusConfiguration): void
    {
        // REGISTER MESSAGE BUS
        $messageBusService = $orkestraConfiguration->service($messageBusConfiguration->serviceId, MessageBus::class);

        // REGISTER MIDDLEWARE
        $this->processMiddleware($orkestraConfiguration, $messageBusConfiguration, $messageBusService);

        // REGISTER MESSAGE HANDLERS
        $this->processMessageHandlers($orkestraConfiguration, $messageBusConfiguration, $messageBusService);

        // Authorizers
        $this->processMessageAuthorizers($orkestraConfiguration, $messageBusConfiguration, $messageBusService);

        // Validators
        $this->processMessageValidators($orkestraConfiguration, $messageBusConfiguration, $messageBusService);

        // Transformers
        $this->processMessagingTransformers($orkestraConfiguration, $messageBusConfiguration, $messageBusService);
    }

    protected function processMiddleware(OrkestraConfiguration $orkestraConfiguration, MessageBusConfiguration $messageBusConfiguration, ServiceConfigurator $messageBusService): void
    {
        $middlewareAsServiceReference = array_map(static function (string $middlewareClassName) use ($orkestraConfiguration) {
            try {
                $orkestraConfiguration->container()->services()->get($middlewareClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($middlewareClassName);
            }

            return service($middlewareClassName);
        }, $messageBusConfiguration->middleware);

        // Add service method call for each middleware to append.
        foreach ($middlewareAsServiceReference as $middlewareServiceReference) {
            $messageBusService->call('addMiddleware', $middlewareServiceReference);
        }
    }

    protected function processMessageHandlers(
        OrkestraConfiguration $orkestraConfiguration,
        MessageBusConfiguration $messageBusConfiguration,
        ServiceConfigurator $messageBusService
    ): void {
        // Register Handlers
        foreach ($messageBusConfiguration->messageHandlers as $handler) {
            try {
                $orkestraConfiguration->container()->services()->get($handler->serviceId);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($handler->serviceId, $handler->className)->lazy()->public();
            }

            $messageBusService->call('addMessageHandler', [service($handler->serviceId)]);
        }

        // Register Message Handler Interceptors
        foreach ($messageBusConfiguration->messageHandlerInterceptors as $interceptorClassName) {
            try {
                $orkestraConfiguration->container()->services()->get($interceptorClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($interceptorClassName);
            }

            $messageBusService->call('addMessageHandlerInterceptor', [service($interceptorClassName)]);
        }
    }

    protected function processMessageAuthorizers(
        OrkestraConfiguration $orkestraConfiguration,
        MessageBusConfiguration $messageBusConfiguration,
        ServiceConfigurator $messageBusService
    ): void {
        foreach ($messageBusConfiguration->authorizers as $authorizerClassName) {
            try {
                $orkestraConfiguration->container()->services()->get($authorizerClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($authorizerClassName);
            }
            $messageBusService->call('addAuthorizer', [service($authorizerClassName)]);
        }
    }

    protected function processMessageValidators(
        OrkestraConfiguration $orkestraConfiguration,
        MessageBusConfiguration $messageBusConfiguration,
        ServiceConfigurator $messageBusService
    ): void {
        foreach ($messageBusConfiguration->validators as $validatorClassName) {
            try {
                $orkestraConfiguration->container()->services()->get($validatorClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($validatorClassName);
            }
            $messageBusService->call('addValidator', [service($validatorClassName)]);
        }
    }

    protected function processMessagingTransformers(
        OrkestraConfiguration $orkestraConfiguration,
        MessageBusConfiguration $messageBusConfiguration,
        ServiceConfigurator $messageBusService
    ): void {
        foreach ($messageBusConfiguration->messagingTransformers as $transformerClassName) {
            try {
                $orkestraConfiguration->container()->services()->get($transformerClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($transformerClassName);
            }
            $messageBusService->call('addTransformer', [service($transformerClassName)]);
        }
    }
}
