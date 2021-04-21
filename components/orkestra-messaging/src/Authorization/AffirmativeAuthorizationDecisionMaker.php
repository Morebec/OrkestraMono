<?php

namespace Morebec\Orkestra\Messaging\Authorization;

use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\Messaging\MessageInterface;

/**
 * Concrete implementation of {@link AuthorizationDecisionMakerInterface} that grants the handling
 * of a Message as soon as any of its inner {@link MessageAuthorizerInterface} grants access ot the handling.
 *
 * TODO: Add tests.
 */
class AffirmativeAuthorizationDecisionMaker implements AuthorizationDecisionMakerInterface
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
        /** @var UnauthorizedException|null $exception */
        $exception = null;
        foreach ($this->authorizers as $authorizer) {
            if (!$authorizer->supportsPreAuthorization($message, $headers)) {
                continue;
            }

            try {
                $authorizer->preAuthorize($message, $headers);
                break; // First Supported authorizer allows the process the request.
            } catch (UnauthorizedException $e) {
                $exception = $e;
            }
        }

        if ($exception) {
            throw $exception;
        }
    }

    public function postAuthorize(MessageInterface $message, MessageHeaders $headers, MessageBusResponseInterface $response): void
    {
        /** @var UnauthorizedException|null $exception */
        $exception = null;
        foreach ($this->authorizers as $authorizer) {
            if (!$authorizer->supportsPostAuthorization($message, $headers, $response)) {
                continue;
            }

            try {
                $authorizer->postAuthorize($message, $headers, $response);
                break; // First Supported authorizer allows the process the request.
            } catch (UnauthorizedException $e) {
                $exception = $e;
            }
        }

        if ($exception) {
            throw $exception;
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

    public function addAuthorizer(MessageAuthorizerInterface $authorizer): void
    {
        $this->authorizers[] = $authorizer;
    }
}
