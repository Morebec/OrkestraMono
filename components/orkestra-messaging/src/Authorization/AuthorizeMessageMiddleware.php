<?php

namespace Morebec\Orkestra\Messaging\Authorization;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;

/**
 * This middleware is responsible for authorizing messages.
 */
class AuthorizeMessageMiddleware implements MessageBusMiddlewareInterface
{
    /**
     * @var AuthorizationDecisionMakerInterface
     */
    private $decisionMaker;

    public function __construct(AuthorizationDecisionMakerInterface $decisionMaker)
    {
        $this->decisionMaker = $decisionMaker;
    }

    public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
    {
        if ($this->decisionMaker->supportsPreAuthorization($message, $headers)) {
            try {
                $this->decisionMaker->preAuthorize($message, $headers);
            } catch (UnauthorizedException $e) {
                return new UnauthorizedResponse($e);
            }
        }

        $response = $next($message, $headers);

        if ($this->decisionMaker->supportsPostAuthorization($message, $headers, $response)) {
            try {
                $this->decisionMaker->postAuthorize($message, $headers, $response);
            } catch (UnauthorizedException $e) {
                return new UnauthorizedResponse($e);
            }
        }

        return $response;
    }
}
