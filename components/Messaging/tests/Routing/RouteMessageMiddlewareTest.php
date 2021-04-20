<?php

namespace Tests\Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Routing\MessageRoute;
use Morebec\Orkestra\Messaging\Routing\MessageRouter;
use Morebec\Orkestra\Messaging\Routing\RouteMessageMiddleware;
use PHPUnit\Framework\TestCase;

class RouteMessageMiddlewareTest extends TestCase
{
    public function testHandle(): void
    {
        $router = new MessageRouter();
        $middleware = new RouteMessageMiddleware($router);

        $message = $this->createMessage();

        $router->registerRoute(new MessageRoute(
            $message::getTypeName(),
            'handler',
            'method'
        ));

        $headers = new MessageHeaders();
        $nextMiddleware = static function (MessageInterface $message, MessageHeaders $headers) {
            return new MessageHandlerResponse('handlerName', MessageBusResponseStatusCode::SUCCEEDED());
        };

        $middleware($message, $headers, $nextMiddleware);

        $this->assertNotEmpty($headers->get(MessageHeaders::DESTINATION_HANDLER_NAMES));
        $this->assertEquals(['handler::method'], $headers->get(MessageHeaders::DESTINATION_HANDLER_NAMES));

        // Test Handler not resolved if destination handler already set.
        $headers = new MessageHeaders([
            MessageHeaders::DESTINATION_HANDLER_NAMES => ['specificHandler::specificMethod'],
        ]);
        $middleware($message, $headers, $nextMiddleware);

        $this->assertEquals(['specificHandler::specificMethod'], $headers->get(MessageHeaders::DESTINATION_HANDLER_NAMES));
    }

    private function createMessage(): MessageInterface
    {
        return new class() implements MessageInterface {
            public static function getTypeName(): string
            {
                return 'message';
            }
        };
    }
}
