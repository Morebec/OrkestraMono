<?php

namespace Morebec\Orkestra\Messaging;

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\Messaging\Authorization\AuthorizationDecisionMakerInterface;
use Morebec\Orkestra\Messaging\Authorization\AuthorizeMessageMiddleware;
use Morebec\Orkestra\Messaging\Authorization\MessageAuthorizerInterface;
use Morebec\Orkestra\Messaging\Authorization\VetoAuthorizationDecisionMaker;
use Morebec\Orkestra\Messaging\Context\BuildMessageBusContextMiddleware;
use Morebec\Orkestra\Messaging\Context\MessageBusContextManager;
use Morebec\Orkestra\Messaging\Context\MessageBusContextManagerInterface;
use Morebec\Orkestra\Messaging\Middleware\LoggerMiddleware;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareCollection;
use Morebec\Orkestra\Messaging\Middleware\MessageBusMiddlewareInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Messaging\Routing\HandleMessageMiddleware;
use Morebec\Orkestra\Messaging\Routing\InMemoryMessageHandlerProvider;
use Morebec\Orkestra\Messaging\Routing\MessageHandlerInterceptorInterface;
use Morebec\Orkestra\Messaging\Routing\MessageRouter;
use Morebec\Orkestra\Messaging\Routing\RouteBuilder;
use Morebec\Orkestra\Messaging\Routing\RouteMessageMiddleware;
use Morebec\Orkestra\Messaging\Transformation\MessagingTransformationMiddleware;
use Morebec\Orkestra\Messaging\Transformation\MessagingTransformerInterface;
use Morebec\Orkestra\Messaging\Validation\MessageValidatorInterface;
use Morebec\Orkestra\Messaging\Validation\ValidateMessageMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionException;

/**
 * Implementation of a message bus that uses the default middleware and which provides convenience methods
 * to interact with it.
 */
class MessageBus implements MessageBusInterface
{
    protected BuildMessageBusContextMiddleware $messageBusContextMiddleware;

    protected LoggerMiddleware $loggerMiddleware;

    protected ValidateMessageMiddleware $validatorMiddleware;

    protected AuthorizeMessageMiddleware $authorizerMiddleware;

    protected MessagingTransformationMiddleware $transformerMiddleware;

    protected RouteMessageMiddleware $routerMiddleware;

    protected HandleMessageMiddleware $handlerMiddleware;

    protected InMemoryMessageHandlerProvider $handlerProvider;
    private MiddlewareMessageBus $messageBus;

    /**
     * SimpleMessageBus constructor.
     *
     * @param LoggerInterface|null                     $logger                     if null a {@link NullLogger} is used
     * @param AuthorizationDecisionMakerInterface|null $authorizationDecisionMaker if null a {@link VetoAuthorizationDecisionMaker} is used
     * @param MessageBusContextManagerInterface|null   $messageBusContextManager   if null a {@link MessageBusContextManager} is used
     */
    public function __construct(
        ClockInterface $clock,
        MessageNormalizerInterface $messageNormalizer = null,
        ?LoggerInterface $logger = null,
        ?AuthorizationDecisionMakerInterface $authorizationDecisionMaker = null,
        ?MessageBusContextManagerInterface $messageBusContextManager = null
    ) {
        $this->messageBusContextMiddleware = new BuildMessageBusContextMiddleware($clock, $messageBusContextManager ?: new MessageBusContextManager());
        $this->loggerMiddleware = new LoggerMiddleware(
            $logger ?: new NullLogger(),
            $messageNormalizer
        );
        $this->validatorMiddleware = new ValidateMessageMiddleware();
        $this->authorizerMiddleware = new AuthorizeMessageMiddleware($authorizationDecisionMaker ?: new VetoAuthorizationDecisionMaker());
        $this->transformerMiddleware = new MessagingTransformationMiddleware();

        $this->routerMiddleware = new RouteMessageMiddleware(new MessageRouter());

        $this->handlerProvider = new InMemoryMessageHandlerProvider();
        $this->handlerMiddleware = new HandleMessageMiddleware($this->handlerProvider);

        $this->messageBus = new MiddlewareMessageBus([
            $this->messageBusContextMiddleware,
            $this->validatorMiddleware,
            $this->authorizerMiddleware,
            $this->transformerMiddleware,
            $this->routerMiddleware,
            $this->handlerMiddleware,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function sendMessage(MessageInterface $message, ?MessageHeaders $headers = null): MessageBusResponseInterface
    {
        return $this->messageBus->sendMessage($message, $headers);
    }

    /**
     * Adds middleware to this message bus right before the {@link HandleMessageMiddleware}.
     */
    public function addMiddleware(MessageBusMiddlewareInterface $middleware): void
    {
        $this->addMiddlewareBefore(\get_class($this->handlerMiddleware), $middleware);
    }

    /**
     * Adds middleware before a given one.
     */
    public function addMiddlewareBefore(string $followingMiddlewareClassName, MessageBusMiddlewareInterface $middleware): void
    {
        $this->messageBus->getMiddleware()->addBefore($followingMiddlewareClassName, $middleware);
    }

    /**
     * Adds middleware after a given one.
     */
    public function addMiddlewareAfter(string $precedingMiddlewareClassName, MessageBusMiddlewareInterface $middleware): void
    {
        $this->messageBus->getMiddleware()->addAfter($precedingMiddlewareClassName, $middleware);
    }

    /**
     * Adds a validator to this message bus.
     *
     * @return $this
     */
    public function addValidator(MessageValidatorInterface $validator): self
    {
        $this->validatorMiddleware->addValidator($validator);

        return $this;
    }

    /**
     * Adds an authorizer to this message bus.
     *
     * @return $this
     */
    public function addAuthorizer(MessageAuthorizerInterface $authorizer): self
    {
        $this->authorizerMiddleware->addAuthorizer($authorizer);

        return $this;
    }

    /**
     * Adds a messaging transformer to this message bus.
     *
     * @return $this
     */
    public function addTransformer(MessagingTransformerInterface $transformer): self
    {
        $this->transformerMiddleware->addTransformer($transformer);

        return $this;
    }

    /**
     * Adds a message handler to this message bus.
     *
     * @return $this
     *
     * @throws ReflectionException
     */
    public function addMessageHandler(MessageHandlerInterface $handler): self
    {
        $this->handlerProvider->addMessageHandler($handler);

        $this->routerMiddleware->registerRoutes(RouteBuilder::forMessageHandler(\get_class($handler))->build());

        return $this;
    }

    /**
     * Adds a message handler interceptor to this message bus.
     *
     * @return $this
     */
    public function addMessageHandlerInterceptor(MessageHandlerInterceptorInterface $interceptor): self
    {
        $this->handlerMiddleware->addInterceptor($interceptor);

        return $this;
    }

    /**
     * Returns the list of middleware in use by the message bus.
     */
    public function getMiddleware(): MessageBusMiddlewareCollection
    {
        return $this->messageBus->getMiddleware();
    }
}
