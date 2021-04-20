<?php

namespace Morebec\Orkestra\Messaging\Routing;

use LogicException;
use Morebec\Orkestra\Exceptions\DomainExceptionInterface;
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandHandlerResponse;
use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;
use Morebec\Orkestra\Messaging\Domain\DomainMessageHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\Event\DomainEventHandlerResponse;
use Morebec\Orkestra\Messaging\Domain\Query\DomainQueryHandlerInterface;
use Morebec\Orkestra\Messaging\Domain\Query\DomainQueryHandlerResponse;
use Morebec\Orkestra\Messaging\Domain\Query\DomainQueryInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageHandlerInterface;
use Morebec\Orkestra\Messaging\MessageHandlerResponse;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;
use Morebec\Orkestra\Messaging\MultiMessageHandlerResponse;
use Throwable;

/**
 * This middleware handles a message and forwards it to the handlers that should receive it,
 * by relying on the routes defined in the headers of a {@link MessageInterface}.
 * It obtains instances of {@link MessageInterface} through the {@link MessageHandlerProviderInterface}.
 */
class HandleMessageMiddleware implements MessageBusMiddlewareInterface
{
    /**
     * @var MessageHandlerProviderInterface
     */
    private $handlerProvider;

    /**
     * @var MessageHandlerInterceptorInterface[]
     */
    private $interceptors;

    public function __construct(
        MessageHandlerProviderInterface $handlerProvider,
        iterable $interceptors = []
    ) {
        $this->handlerProvider = $handlerProvider;
        $this->interceptors = [];
        foreach ($interceptors as $interceptor) {
            $this->addInterceptor($interceptor);
        }
    }

    public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
    {
        $routes = $headers->get(MessageHeaders::DESTINATION_HANDLER_NAMES, []);

        $responses = [];
        /** @var string $route */
        foreach ($routes as $route) {
            [$handlerClassName, $handlerMethodName] = explode('::', $route);

            $handler = $this->handlerProvider->getMessageHandler($handlerClassName);

            if (!$handler) {
                throw new LogicException(sprintf('Message Handler "%s" was not found.', $handlerClassName));
            }

            // Invoke Interceptors
            $context = new MessageHandlerInterceptionContext($message, $headers, $handler, $handlerMethodName);
            foreach ($this->interceptors as $interceptor) {
                $interceptor->beforeHandle($context);
            }

            // Check for replaced values
            $message = $context->getMessage();
            $headers = $context->getMessageHeaders();
            $handlerMethodName = $context->getMessageHandlerMethodName();
            $handler = $context->getMessageHandler();

            if (!$handler) {
                continue;
            }

            // Invoke handler
            $response = $this->invokeMessageHandler($message, $handler, $handlerMethodName);

            // Invoke interceptors
            $context->replaceResponse($response);
            foreach ($this->interceptors as $interceptor) {
                $interceptor->afterHandle($context);
            }
            // Check for replaced values
            $response = $context->getResponse();

            $responses[] = $response;
        }

        $finalResponse = $this->buildSingleResponseFromArrayResponses($responses);

        if ($finalResponse instanceof UnhandledMessageResponse) {
            if ($message instanceof DomainCommandInterface || $message instanceof DomainQueryInterface) {
                throw new UnhandledMessageException($message);
            }
        }

        return $finalResponse;
    }

    /**
     * Adds an interceptor to this message middleware.
     */
    public function addInterceptor(MessageHandlerInterceptorInterface $interceptor): void
    {
        $this->interceptors[] = $interceptor;
    }

    /**
     * Invokes a message handler.
     */
    protected function invokeMessageHandler(MessageInterface $message, MessageHandlerInterface $handler, string $handlerMethodName): MessageBusResponseInterface
    {
        try {
            $payload = $handler->{$handlerMethodName}($message);
        } catch (Throwable $throwable) {
            $payload = $throwable;
        }

        return $this->buildResponseForMessageHandlerPayload($handler, $payload);
    }

    /**
     * Builds a sensible response for a handler invocation according to the returned payload.
     */
    protected function buildResponseForMessageHandlerPayload(MessageHandlerInterface $messageHandler, $payload): MessageBusResponseInterface
    {
        if ($payload instanceof MessageBusResponseInterface) {
            return $payload;
        }

        if ($payload instanceof MessageBusResponseStatusCode) {
            $statusCode = $payload;
            $payload = null;
        } else {
            if ($payload instanceof Throwable) {
                if ($payload instanceof DomainExceptionInterface) {
                    $statusCode = MessageBusResponseStatusCode::REFUSED();
                } else {
                    $statusCode = MessageBusResponseStatusCode::FAILED();
                }
            } else {
                $statusCode = MessageBusResponseStatusCode::SUCCEEDED();
            }
        }

        $messageHandlerName = \get_class($messageHandler);

        if ($messageHandler instanceof DomainMessageHandlerInterface) {
            if ($messageHandler instanceof DomainCommandHandlerInterface) {
                return new DomainCommandHandlerResponse($messageHandlerName, $statusCode, $payload);
            }

            if ($messageHandler instanceof DomainEventHandlerInterface) {
                return new DomainEventHandlerResponse($messageHandlerName, $statusCode, $payload);
            }

            if ($messageHandler instanceof DomainQueryHandlerInterface) {
                return new DomainQueryHandlerResponse($messageHandlerName, $statusCode, $payload);
            }

            return new MessageHandlerResponse($messageHandlerName, $statusCode, $payload);
        }

        return new MessageHandlerResponse($messageHandlerName, $statusCode, $payload);
    }

    /**
     * Returns a response using the list of responses for all the handlers.
     */
    protected function buildSingleResponseFromArrayResponses(array $responses): MessageBusResponseInterface
    {
        // Determine the type of response we must provide.
        $nbResponses = \count($responses);

        if ($nbResponses === 0) {
            return new UnhandledMessageResponse();
        }

        if ($nbResponses === 1) {
            return $responses[0];
        }

        return new MultiMessageHandlerResponse($responses);
    }
}
