<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection;

use Morebec\Orkestra\Messaging\Routing\MessageHandlerRouteBuilder;
use Morebec\Orkestra\Messaging\Routing\MessageRouteCollection;
use Morebec\Orkestra\Messaging\Routing\MessageRouterInterface;
use Morebec\Orkestra\SymfonyBundle\Messaging\MessageRouterCache;
use Morebec\Orkestra\SymfonyBundle\Module\AutoRoutedMessageHandlerServiceConfigurator;
use Morebec\Orkestra\SymfonyBundle\Module\MessageHandlerServiceConfigurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compiler pass checks for messaging routing information in service tags definition
 * to determine how to configure the router for the different message.handlers as they were defined using the {@link MessageHandlerServiceConfigurator}.
 */
class RegisterRoutesForMessageHandlersCompilerPass implements CompilerPassInterface
{
    /**
     * @var MessageRouterCache
     */
    private $routerCache;

    public function __construct(MessageRouterCache $routerCache)
    {
        $this->routerCache = $routerCache;
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(MessageRouterInterface::class)) {
            return;
        }

        $routes = new MessageRouteCollection();

        $messageHandlerIds = $container->findTaggedServiceIds(MessageHandlerServiceConfigurator::MESSAGING_HANDLER_TAG);

        foreach ($messageHandlerIds as $serviceId => $_) {
            $definition = $container->getDefinition($serviceId);
            $tags = $definition->getTags();

            $autoroute = false;
            $disabledMethods = [];

            foreach ($tags as $tag => $attributes) {
                switch ($tag) {
                    case AutoRoutedMessageHandlerServiceConfigurator::MESSAGING_ROUTING_AUTOROUTE_TAG:
                        $autoroute = true;
                        break;

                    case AutoRoutedMessageHandlerServiceConfigurator::MESSAGING_ROUTING_DISABLED_METHOD_TAG:
                        foreach ($attributes as $attribute) {
                            $methodName = $attribute['name'];
                            $disabledMethods[$methodName] = $methodName;
                        }
                        break;
                }
            }

            if (!$autoroute) {
                continue;
            }

            $builder = MessageHandlerRouteBuilder::forMessageHandler($serviceId);
            foreach ($disabledMethods as $disabledMethod) {
                $builder->withMethodDisabled($disabledMethod);
            }

            $routes->addAll($builder->build());
        }

        // Symfony does not allow injecting objects as method calls in the container.
        // To overcome this, we have to dump the routes in a file in cache along with the container
        // and provide it a way to load these routes.
        $this->routerCache->dumpRoutes($routes);
    }
}
