<?php

namespace Morebec\OrkestraSymfonyBundle\Messaging;

use Morebec\Orkestra\Messaging\Routing\MessageRouter;
use Morebec\Orkestra\SymfonyBundle\Messaging\MessageRouterCache;

/**
 * Implementation of a message router that loads its routes from a Symfony cache.
 */
class CachedMessageRouter extends MessageRouter
{
    public function __construct(MessageRouterCache $cache)
    {
        $routes = $cache->loadRoutes();
        parent::__construct($routes->toArray());
    }
}
