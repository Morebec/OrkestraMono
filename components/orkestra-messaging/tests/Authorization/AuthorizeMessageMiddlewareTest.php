<?php

namespace Tests\Morebec\Orkestra\Messaging\Authorization;

use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\Authorization\MessageAuthorizerInterface;
use Morebec\Orkestra\Messaging\Authorization\UnauthorizedException;
use Morebec\Orkestra\Messaging\Authorization\UnauthorizedResponse;
use Morebec\Orkestra\Messaging\Authorization\VetoAuthorizationDecisionMaker;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use PHPUnit\Framework\TestCase;

class AuthorizeMessageMiddlewareTest extends TestCase
{
    public function testInvoke(): void
    {
        $authorizer = $this->createAuthorizer();
        $middleware = new AuthorizeMessageMiddleware(
            new VetoAuthorizationDecisionMaker([$authorizer])
        );

        $message = $this->createMessage();

        $nextMiddleware = static function (MessageInterface $message, MessageHeaders $headers) {
            return new MessageHandlerResponse('handlerName', MessageBusResponseStatusCode::SUCCEEDED());
        };

        $response = $middleware($message, new MessageHeaders(), $nextMiddleware);

        $this->assertInstanceOf(UnauthorizedResponse::class, $response);
    }

    private function createAuthorizer(): MessageAuthorizerInterface
    {
        return new class() implements MessageAuthorizerInterface {
            public function authorize(MessageInterface $message, MessageHeaders $messageHeaders): void
            {
                throw new UnauthorizedException('Not authorized');
            }

            public function preAuthorize(MessageInterface $message, MessageHeaders $headers): void
            {
                throw new UnauthorizedException('Not authorized');
            }

            public function postAuthorize(MessageInterface $message, MessageHeaders $headers, MessageBusResponseInterface $response): void
            {
            }

            public function supportsPreAuthorization(MessageInterface $message, MessageHeaders $headers): bool
            {
                return true;
            }

            public function supportsPostAuthorization(MessageInterface $message, MessageHeaders $headers, MessageBusResponseInterface $response): bool
            {
                return false;
            }
        };
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
