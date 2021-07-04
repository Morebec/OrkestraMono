<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use JsonException;
use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\Context\BuildMessageBusContextMiddleware;
use Morebec\Orkestra\Messaging\Context\MessageBusContextManager;
use Morebec\Orkestra\Messaging\Context\MessageBusContextManagerInterface;
use Morebec\Orkestra\Messaging\Context\MessageBusContextProvider;
use Morebec\Orkestra\Messaging\Context\MessageBusContextProviderInterface;
use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\Routing\HandleMessageMiddleware;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerRouteBuilder;
use Morebec\Orkestra\Messaging\Routing\MessageRouteCollection;
use Morebec\Orkestra\Messaging\Routing\RouteMessageMiddleware;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;
use Morebec\Orkestra\SymfonyBundle\Messaging\MessageRouterCache;
use Morebec\Orkestra\SymfonyBundle\OrkestraKernel;
use ReflectionException;
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
    private OrkestraKernel $kernel;

    public function __construct(OrkestraKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function process(OrkestraConfiguration $orkestraConfiguration, MessageBusConfiguration $messageBusConfiguration): void
    {
        // REGISTER MESSAGE BUS
        $messageBusService = $orkestraConfiguration->service(MessageBusInterface::class, $messageBusConfiguration->implementationClassName);

        // REGISTER MIDDLEWARE
        $this->processMiddleware($orkestraConfiguration, $messageBusConfiguration, $messageBusService);

        // REGISTER MESSAGE HANDLERS
        $this->processMessageHandlers($orkestraConfiguration, $messageBusConfiguration);

        // Authorizers
        $this->processMessageAuthorizers($orkestraConfiguration, $messageBusConfiguration);

        // Validators
        $this->processMessageValidators($orkestraConfiguration, $messageBusConfiguration);
    }

    public function processMiddleware(OrkestraConfiguration $orkestraConfiguration, MessageBusConfiguration $messageBusConfiguration, ServiceConfigurator $messageBusService): void
    {
        $middlewareAsServiceReference = array_map(static function (string $middlewareClassName) use ($orkestraConfiguration) {
            if ($middlewareClassName === BuildMessageBusContextMiddleware::class) {
                // Register dependencies.
                try {
                    $orkestraConfiguration->container()->services()->get(MessageBusContextManagerInterface::class);
                } catch (ServiceNotFoundException $exception) {
                    $orkestraConfiguration->service(MessageBusContextManagerInterface::class, MessageBusContextManager::class);
                }

                try {
                    $orkestraConfiguration->container()->services()->get(MessageBusContextProviderInterface::class);
                } catch (ServiceNotFoundException $exception) {
                    $orkestraConfiguration->service(MessageBusContextProviderInterface::class, MessageBusContextProvider::class);
                }
            }

            try {
                $orkestraConfiguration->container()->services()->get($middlewareClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($middlewareClassName);
            }

            return service($middlewareClassName);
        }, $messageBusConfiguration->middleware);

        // Add service method call for each middleware to append.
        if ($messageBusConfiguration->implementationClassName === DefaultMessageBusConfiguration::DEFAULT_IMPLEMENTATION_CLASS_NAME) {
            $messageBusService->args([$middlewareAsServiceReference]);
        } else {
            foreach ($middlewareAsServiceReference as $middlewareServiceReference) {
                $messageBusService->call('appendMiddleware', $middlewareServiceReference);
            }
        }
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function processMessageHandlers(
        OrkestraConfiguration $orkestraConfiguration,
        MessageBusConfiguration $messageBusConfiguration
    ): void {
        try {
            $handleMessageMiddlewareService = $orkestraConfiguration->container()->services()->get(HandleMessageMiddleware::class);
        } catch (ServiceNotFoundException $exception) {
            $handleMessageMiddlewareService = null;
        }

        if ($handleMessageMiddlewareService && $messageBusConfiguration instanceof DefaultMessageBusConfiguration) {
            $routerCache = new MessageRouterCache($this->kernel->getCacheDir());
            $routes = new MessageRouteCollection();

            try {
                $routeMiddlewareService = $orkestraConfiguration->container()->services()->get(RouteMessageMiddleware::class);
            } catch (ServiceNotFoundException $exception) {
                $routeMiddlewareService = null;
            }

            foreach ($messageBusConfiguration->messageHandlers as $handler) {
                try {
                    $orkestraConfiguration->container()->services()->get($handler->serviceId);
                } catch (ServiceNotFoundException $exception) {
                    $orkestraConfiguration->service($handler->serviceId, $handler->className)->lazy()->public();
                }

                if ($handler->autoroute && $routeMiddlewareService) {
                    $routes->addAll(
                        MessageHandlerRouteBuilder::forMessageHandler($handler->serviceId)
                            ->build()
                    );
                }
            }
            // Symfony does not allow injecting objects as method calls in the container.
            // To overcome this, we have to dump the routes in a file in cache along with the container
            // and provide it a way to load these routes.
            $routerCache->dumpRoutes($routes);

            // Register Message Handler Interceptors
            foreach ($messageBusConfiguration->messageHandlerInterceptors as $interceptorClassName) {
                try {
                    $orkestraConfiguration->container()->services()->get($interceptorClassName);
                } catch (ServiceNotFoundException $exception) {
                    $orkestraConfiguration->service($interceptorClassName);
                }

                $handleMessageMiddlewareService->call('addInterceptor', [
                    service($interceptorClassName),
                ]);
            }
        }
    }

    private function processMessageAuthorizers(
        OrkestraConfiguration $orkestraConfiguration,
        MessageBusConfiguration $messageBusConfiguration
    ): void {
        if (!($messageBusConfiguration instanceof DefaultMessageBusConfiguration)) {
            return;
        }

        try {
            $authorizeMessageMiddlewareService = $orkestraConfiguration
                ->container()
                ->services()
                ->get(AuthorizeMessageMiddleware::class)
            ;
        } catch (ServiceNotFoundException $exception) {
            return;
        }

        $authorizerServiceReferences = [];
        foreach ($messageBusConfiguration->authorizers as $authorizerClassName) {
            try {
                $orkestraConfiguration->container()->services()->get($authorizerClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($authorizerClassName);
            }
            $authorizerServiceReferences[] = service($authorizerClassName);
        }

        $authorizeMessageMiddlewareService->args([
            $authorizerServiceReferences,
        ]);
    }

    private function processMessageValidators(
        OrkestraConfiguration $orkestraConfiguration,
        MessageBusConfiguration $messageBusConfiguration
    ): void {
        if (!($messageBusConfiguration instanceof DefaultMessageBusConfiguration)) {
            return;
        }

        try {
            $validateMessageMiddlewareService = $orkestraConfiguration
                ->container()
                ->services()
                ->get(ValidateMessageMiddleware::class)
            ;
        } catch (ServiceNotFoundException $exception) {
            return;
        }

        $validatorServiceReferences = [];
        foreach ($messageBusConfiguration->validators as $validatorClassName) {
            try {
                $orkestraConfiguration->container()->services()->get($validatorClassName);
            } catch (ServiceNotFoundException $exception) {
                $orkestraConfiguration->service($validatorClassName);
            }
            $validatorServiceReferences[] = service($validatorClassName);
        }

        $validateMessageMiddlewareService->args([
            $validatorServiceReferences,
        ]);
    }
}
