<?php

namespace Morebec\Orkestra\Messaging\Authorization;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Concrete implementation of {@link AuthorizationDecisionMakerInterface} that denies the handling
 * of a Message as soon as any of its inner {@link MessageAuthorizerInterface} denies access ot the handling.
 *
 * TODO: Add tests.
 */
class VetoAuthorizationDecisionMaker implements AuthorizationDecisionMakerInterface
{
    /**
     * @var MessageAuthorizerInterface[]
     */
    private $authorizers;

    public function __construct(iterable $authorizers = [])
    {
        $this->authorizers = [];
        foreach ($authorizers as $authorizer) {
            $this->addAuthorizer($authorizer);
        }
    }

    public function preAuthorize(MessageInterface $message, MessageHeaders $headers): void
    {
        foreach ($this->authorizers as $authorizer) {
            if (!$authorizer->supportsPreAuthorization($message, $headers)) {
                continue;
            }

            $authorizer->preAuthorize($message, $headers);
        }
    }

    public function postAuthorize(MessageInterface $message, MessageHeaders $headers, MessageBusResponseInterface $response): void
    {
        foreach ($this->authorizers as $authorizer) {
            if (!$authorizer->supportsPostAuthorization($message, $headers, $response)) {
                continue;
            }

            $authorizer->postAuthorize($message, $headers, $response);
        }
    }

    public function supportsPreAuthorization(MessageInterface $message, MessageHeaders $headers): bool
    {
        foreach ($this->authorizers as $authorizer) {
            if ($authorizer->supportsPreAuthorization($message, $headers)) {
                return true;
            }
        }

        return false;
    }

    public function supportsPostAuthorization(MessageInterface $message, MessageHeaders $headers, MessageBusResponseInterface $response): bool
    {
        foreach ($this->authorizers as $authorizer) {
            if ($authorizer->supportsPostAuthorization($message, $headers, $response)) {
                return true;
            }
        }

        return false;
    }

    public function addAuthorizer($authorizer): void
    {
        $this->authorizers[] = $authorizer;
    }
}
