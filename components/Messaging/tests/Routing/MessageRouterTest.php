<?php

namespace Tests\Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Routing\MessageRoute;
use Morebec\Orkestra\Messaging\Routing\MessageRouter;
use PHPUnit\Framework\TestCase;

class MessageRouterTest extends TestCase
{
    public function testGetRoutes(): void
    {
        $router = new MessageRouter();
        $this->assertEmpty($router->getRoutes());

        $router->registerRoute(new MessageRoute('message', 'handler', 'method'));
        $this->assertCount(1, $router->getRoutes());
    }

    public function testClearRoutes(): void
    {
        $router = new MessageRouter();
        $router->registerRoute(new MessageRoute('message', 'handler', 'method'));

        $router->clearRoutes();
        $this->assertEmpty($router->getRoutes());
    }

    public function testRegisterRoute(): void
    {
        $router = new MessageRouter();

        // Adding twice the same route should not add it twice.
        $router->registerRoute(new MessageRoute('message', 'handler', 'method'));
        $router->registerRoute(new MessageRoute('message', 'handler', 'method'));

        $this->assertCount(1, $router->getRoutes());
    }

    public function testRegisterRoutes(): void
    {
        $router = new MessageRouter();

        // Adding twice the same route should not add it twice.
        $router->registerRoutes(
            [
                new MessageRoute('message', 'handler', 'method'),
                new MessageRoute('message', 'handler', 'method'),
                new MessageRoute('message', 'handler2', 'method'),
            ]
        );

        $this->assertCount(2, $router->getRoutes());
    }

    public function testRouteMessage(): void
    {
        $router = new MessageRouter();

        // Adding twice the same route should not add it twice.
        $router->registerRoutes(
            [
                new MessageRoute('message', 'handler', 'method'),
                new MessageRoute('message', 'handler2', 'method'),
                new MessageRoute('messageA', 'handler2', 'method'),
            ]
        );

        $message = $this->createMessage();
        $routes = $router->routeMessage($message, new MessageHeaders());

        $this->assertCount(2, $routes);
        $routesArray = $routes->toArray();
        $this->assertEquals(
            new MessageRoute('message', 'handler', 'method'),
            $routesArray[0]
        );
        $this->assertEquals(
            new MessageRoute('message', 'handler2', 'method'),
            $routesArray[1]
        );
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
