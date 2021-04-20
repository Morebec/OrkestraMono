<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Psr\Log\LoggerInterface;

/**
 * Interceptor that logs before a message is sent to a handler and after a response is received form that handler.
 */
class LoggingMessageHandlerInterceptor implements MessageHandlerInterceptorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var MessageNormalizerInterface
     */
    private $messageNormalizer;

    public function __construct(LoggerInterface $logger, MessageNormalizerInterface $messageNormalizer)
    {
        $this->logger = $logger;
        $this->messageNormalizer = $messageNormalizer;
    }

    public function beforeHandle(MessageHandlerInterceptionContext $context): void
    {
        $message = $context->getMessage();
        $headers = $context->getMessageHeaders();
        $handlerClassName = \get_class($context->getMessageHandler());
        $handlerMethodName = $context->getMessageHandlerMethodName();

        $this->logger->info('Sending message of type "{messageType}" to message handler "{messageHandlerName}::{messageHandlerMethodName}".', [
            'messageType' => $message::getTypeName(),
            'messageHeaders' => $headers->toArray(),
            'message' => $this->messageNormalizer->normalize($message),
            'messageHandlerName' => $handlerClassName,
            'messageHandlerMethodName' => $handlerMethodName,
        ]);
    }

    public function afterHandle(MessageHandlerInterceptionContext $context): void
    {
        $message = $context->getMessage();
        $handlerClassName = \get_class($context->getMessageHandler());
        $handlerMethodName = $context->getMessageHandlerMethodName();
        $response = $context->getResponse();

        $this->logger->info('Message of type "{messageType}" received by handler "{messageHandlerName}::{messageHandlerMethodName}".', [
            'messageType' => $message::getTypeName(),
            'messageHandlerName' => $handlerClassName,
            'messageHandlerMethodName' => $handlerMethodName,
            'responseStatusCode' => (string) $response->getStatusCode(),
            'responseFailed' => $response->isFailure(),
        ]);
    }
}
