<?php

namespace Tests\Morebec\Orkestra\Messaging;

use Morebec\Orkestra\DateTime\SystemClock;
use Morebec\Orkestra\Messaging\Authorization\MessageAuthorizerInterface;
use Morebec\Orkestra\Messaging\Authorization\UnauthorizedException;
use Morebec\Orkestra\Messaging\Authorization\UnauthorizedResponse;
use Morebec\Orkestra\Messaging\Context\BuildMessageBusContextMiddleware;
use Morebec\Orkestra\Messaging\MessageBus;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Messaging\Routing\HandleMessageMiddleware;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerInterceptionContext;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerInterceptorInterface;
use Morebec\Orkestra\Messaging\Transformation\AbstractResponseTransformer;
use Morebec\Orkestra\Messaging\Validation\InvalidMessageResponse;
use Morebec\Orkestra\Messaging\Validation\MessageValidationError;
use Morebec\Orkestra\Messaging\Validation\MessageValidationErrorList;
use Morebec\Orkestra\Messaging\Validation\MessageValidatorInterface;
use PHPUnit\Framework\TestCase;

class MessageBusTest extends TestCase
{
    public function testSendMessage(): void
    {
        $clock = new SystemClock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageBus = new MessageBus($clock, $messageNormalizer);

        $response = $messageBus->sendMessage($this->createMessage());

        self::assertTrue($response->isSuccess());
        self::assertNull($response->getPayload());
        self::assertEquals(MessageBusResponseStatusCode::SKIPPED(), $response->getStatusCode());
    }

    public function testAddMiddleware(): void
    {
        $clock = new SystemClock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageBus = new MessageBus($clock, $messageNormalizer);

        $middleware = $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock();
        $messageBus->addMiddleware($middleware);

        self::assertContains($middleware, $messageBus->getMiddleware()->toArray());
    }

    public function testAddMiddlewareBefore(): void
    {
        $clock = new SystemClock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageBus = new MessageBus($clock, $messageNormalizer);

        $middleware = $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock();
        $messageBus->addMiddlewareBefore(BuildMessageBusContextMiddleware::class, $middleware);

        self::assertEquals($middleware, $messageBus->getMiddleware()->getOrDefault(0));
    }

    public function testAddMiddlewareAfter(): void
    {
        $clock = new SystemClock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageBus = new MessageBus($clock, $messageNormalizer);

        $middleware = $this->getMockBuilder(MessageBusMiddlewareInterface::class)->getMock();
        $messageBus->addMiddlewareAfter(HandleMessageMiddleware::class, $middleware);

        $middlewareCollection = $messageBus->getMiddleware();
        self::assertEquals($middleware, $middlewareCollection->getOrDefault(\count($middlewareCollection) - 1));
    }

    public function testAddValidator(): void
    {
        $clock = new SystemClock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageBus = new MessageBus($clock, $messageNormalizer);
        $messageBus->addValidator(new class() implements MessageValidatorInterface {
            public function validate(MessageInterface $message, MessageHeaders $headers): MessageValidationErrorList
            {
                return new MessageValidationErrorList([
                    new MessageValidationError(
                        'invalid message',
                        'hello',
                        'world'
                    ),
                ]);
            }

            public function supports(MessageInterface $message, MessageHeaders $headers): bool
            {
                return true;
            }
        });

        $response = $messageBus->sendMessage($this->createMessage());

        self::assertInstanceOf(InvalidMessageResponse::class, $response);
    }

    public function testAddAuthorizer(): void
    {
        $clock = new SystemClock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageBus = new MessageBus($clock, $messageNormalizer);
        $messageBus->addAuthorizer(new class() implements MessageAuthorizerInterface {
            public function preAuthorize(MessageInterface $message, MessageHeaders $headers): void
            {
                throw new UnauthorizedException();
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
        });
        $response = $messageBus->sendMessage($this->createMessage());

        self::assertInstanceOf(UnauthorizedResponse::class, $response);
    }

    public function testAddTransformer(): void
    {
        $clock = new SystemClock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageBus = new MessageBus($clock, $messageNormalizer);
        $messageBus->addTransformer(new class() extends AbstractResponseTransformer {
            public function transformResponse(MessageBusResponseInterface $response, MessageInterface $message, MessageHeaders $headers): MessageBusResponseInterface
            {
                return new UnauthorizedResponse(new UnauthorizedException());
            }
        });

        $response = $messageBus->sendMessage($this->createMessage());

        self::assertInstanceOf(UnauthorizedResponse::class, $response);
    }

    public function testAddMessageHandler(): void
    {
        $clock = new SystemClock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageBus = new MessageBus($clock, $messageNormalizer);

        $messageBus->addMessageHandler(new class() implements MessageHandlerInterface {
            public function __invoke(MessageInterface $message)
            {
                return 'handler_invoked';
            }
        });

        $response = $messageBus->sendMessage($this->createMessage());

        self::assertTrue($response->isSuccess());
        self::assertEquals(MessageBusResponseStatusCode::SUCCEEDED(), $response->getStatusCode());
        self::assertEquals('handler_invoked', $response->getPayload());
    }

    public function testAddMessageHandlerInterceptor(): void
    {
        $clock = new SystemClock();
        $messageNormalizer = $this->getMockBuilder(MessageNormalizerInterface::class)->getMock();
        $messageBus = new MessageBus($clock, $messageNormalizer);

        // Add handler so interceptor can be invoked.
        $messageBus->addMessageHandler(new class() implements MessageHandlerInterface {
            public function __invoke(MessageInterface $message)
            {
                return 'handler_invoked';
            }
        });
        $messageBus->addMessageHandlerInterceptor(new class() implements MessageHandlerInterceptorInterface {
            public function beforeHandle(MessageHandlerInterceptionContext $context): void
            {
                // TODO: Implement beforeHandle() method.
            }

            public function afterHandle(MessageHandlerInterceptionContext $context): void
            {
                $context->replaceResponse(new UnauthorizedResponse(new UnauthorizedException()));
            }
        });

        $response = $messageBus->sendMessage($this->createMessage());

        self::assertInstanceOf(UnauthorizedResponse::class, $response);
    }

    private function createMessage(): MessageInterface
    {
        return new class() implements MessageInterface {
            public static function getTypeName(): string
            {
                return 'message.unit_test';
            }
        };
    }
}
