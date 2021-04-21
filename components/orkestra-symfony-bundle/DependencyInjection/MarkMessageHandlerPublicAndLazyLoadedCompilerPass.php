<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection;

use Morebec\Orkestra\Messaging\MessageHandlerInterface;
use Morebec\Orkestra\Messaging\Routing\ContainerMessageHandlerProvider;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compiler pass marks message handlers as public and lazy loaded
 * if the {@link MessageHandlerProviderInterface} is implemented by the {@link ContainerMessageHandlerProvider}.
 */
class MarkMessageHandlerPublicAndLazyLoadedCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(MessageHandlerProviderInterface::class)) {
            return;
        }

        $provider = $container->getDefinition(MessageHandlerProviderInterface::class);
        if ($provider->getClass() !== ContainerMessageHandlerProvider::class) {
            return;
        }

        foreach ($container->getDefinitions() as $definition) {
            if (!is_a($definition->getClass(), MessageHandlerInterface::class, true)) {
                continue;
            }

            $definition->setPublic(true);
            $definition->setLazy(true);
        }
    }
}
