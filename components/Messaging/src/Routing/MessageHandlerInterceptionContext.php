<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHandlerInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Represents the context in which an interceptor can perform work.
 * In order for a message handler to alter a message, its headers or final response.
 */
class MessageHandlerInterceptionContext
{
    /** @var MessageInterface */
    private $message;

    /** @var MessageHeaders */
    private $messageHeaders;

    /** @var MessageBusResponseInterface|null */
    private $response;

    /**
     * @var MessageHandlerInterface|null
     */
    private $messageHandler;

    /**
     * @var string|null
     */
    private $messageHandlerMethodName;

    public function __construct(
        MessageInterface $message,
        MessageHeaders $messageHeaders,
        MessageHandlerInterface $messageHandler,
        string $handlerMethod,
        ?MessageBusResponseInterface $response = null
    ) {
        $this->message = $message;
        $this->messageHeaders = $messageHeaders;
        $this->messageHandler = $messageHandler;
        $this->messageHandlerMethodName = $handlerMethod;
        $this->response = $response;
    }

    public function replaceMessage(MessageInterface $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function replaceMessageHeaders(MessageHeaders $messageHeaders): void
    {
        $this->messageHeaders = $messageHeaders;
    }

    /**
     * Adds headers to the message headers.
     * If some  headers already exist they are replaced by default.
     */
    public function addMessageHeaders(array $headers, bool $replace = true): void
    {
        foreach ($headers as $key => $value) {
            if ($this->messageHeaders->has($key) && !$replace) {
                continue;
            }

            $this->messageHeaders->set($key, $value);
        }
    }

    /**
     * Removes headers from the {@link MessageHeaders}.
     */
    public function removeMessageHeaders(array $keys): void
    {
        foreach ($keys as $key) {
            $this->messageHeaders->remove($key);
        }
    }

    public function getMessageHeaders(): MessageHeaders
    {
        return $this->messageHeaders;
    }

    public function replaceMessageHandler(MessageHandlerInterface $messageHandler, string $handlerMethod): void
    {
        $this->messageHandler = $messageHandler;
        $this->messageHandlerMethodName = $handlerMethod;
    }

    public function getMessageHandler(): ?MessageHandlerInterface
    {
        return $this->messageHandler;
    }

    /**
     * This method changes the context to skip the message handler.
     */
    public function skipMessageHandler(): void
    {
        $this->messageHandler = null;
        $this->messageHandlerMethodName = null;
    }

    public function getMessageHandlerMethodName(): string
    {
        return $this->messageHandlerMethodName;
    }

    public function getResponse(): ?MessageBusResponseInterface
    {
        return $this->response;
    }

    /**
     * Replaces the response.
     */
    public function replaceResponse(MessageBusResponseInterface $response): void
    {
        $this->response = $response;
    }
}
