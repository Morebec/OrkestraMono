<?php

namespace Morebec\Orkestra\SymfonyBundle;

use Morebec\Orkestra\SymfonyBundle\DependencyInjection\MarkMessageHandlerPublicAndLazyLoadedCompilerPass;
use Morebec\Orkestra\SymfonyBundle\DependencyInjection\RegisterRoutesForMessageHandlersCompilerPass;
use Morebec\Orkestra\SymfonyBundle\Messaging\MessageRouterCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OrkestraSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MarkMessageHandlerPublicAndLazyLoadedCompilerPass());

        $routerCache = new MessageRouterCache($container->getParameter('kernel.cache_dir'));
        $container->addCompilerPass(new RegisterRoutesForMessageHandlersCompilerPass($routerCache));
    }

    public function boot()
    {
    }

    public function shutdown()
    {
    }
}
