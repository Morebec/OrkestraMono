<?php

namespace Morebec\Orkestra\OrkestraServer\Messaging;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;

/**
 * Message Bus Middleware routing messages to Services.
 */
class RouteServiceMessageMiddleware implements MessageBusMiddlewareInterface
{
    /**
     * @var ServiceRepositoryInterface
     */
    private $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
    {
    }
}
