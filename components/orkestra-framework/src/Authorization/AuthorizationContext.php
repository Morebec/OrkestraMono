<?php

namespace Morebec\Orkestra\Framework\Authorization;

use Morebec\Orkestra\Messaging\MessageInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The authorization context is a type of object passed to Security Voters in order to pass more than a single object.
 * This is used by the code generated by the spectool.
 */
class AuthorizationContext
{
    private Request $request;

    private MessageInterface $message;

    public function __construct(
        Request $request,
        MessageInterface $message
    ) {
        $this->request = $request;
        $this->message = $message;
    }

    /**
     * Returns the request that triggered authorization.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns the method of the request.
     */
    public function getRequestMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * Returns a request attribute.
     *
     * @return mixed
     */
    public function getRequestAttribute(string $attribute)
    {
        return $this->request->attributes->get($attribute);
    }

    /**
     * Returns the message that should be sent on the message bus if authorization passes.
     */
    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    /**
     * Indicates if the type name of a message is equal to a given one.
     */
    public function isMessageTypeName(string $typeName): bool
    {
        return $this->getMessageTypeName() === $typeName;
    }

    /**
     * Returns the type name of the message.
     */
    public function getMessageTypeName(): string
    {
        return $this->message::getTypeName();
    }

    /**
     * Returns the class Name of a Message.
     */
    public function getMessageClassName(): string
    {
        return \get_class($this->message);
    }
}
