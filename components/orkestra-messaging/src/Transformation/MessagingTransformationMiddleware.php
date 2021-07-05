<?php

namespace Morebec\Orkestra\Messaging\Transformation;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;

/**
 * This middleware allows to define a series of simple transformers for messages and responses.
 * It acts as an interception mechanism, that does not require creating new middleware for every interception use cases.
 * One simply needs to implement the {@link MessagingTransformerInterface} and add it to this middleware.
 *
 * Essentially it allows to reduce the configuration for the middleware chain.
 */
class MessagingTransformationMiddleware implements MessageBusMiddlewareInterface
{
    /**
     * @var MessagingTransformerInterface[]
     */
    private array $transformers;

    public function __construct(iterable $transformers)
    {
        $this->transformers = [];
        foreach ($transformers as $transformer) {
            $this->addTransformer($transformer);
        }
    }

    public function __invoke(MessageInterface $message, MessageHeaders $headers, callable $next): MessageBusResponseInterface
    {
        foreach ($this->transformers as $transformer) {
            $message = $transformer->transformMessage($message, $headers);
        }

        /** @var MessageBusResponseInterface $response */
        $response = $next($message, $headers);

        foreach ($this->transformers as $transformer) {
            $response = $transformer->transformResponse($response, $message, $headers);
        }

        return $response;
    }

    /**
     * Adds a transformer to this middleware pipeline.
     */
    public function addTransformer(MessagingTransformerInterface $transformer): void
    {
        $this->transformers[] = $transformer;
    }
}
