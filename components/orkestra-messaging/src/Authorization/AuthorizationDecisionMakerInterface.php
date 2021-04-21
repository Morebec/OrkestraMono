<?php

namespace Morebec\Orkestra\Messaging\Authorization;

/**
 * Makes a final decision about whether or not some messages should be handled.
 * It relies on {@link MessageAuthorizerInterface} and determines based on their answer
 * a final decision.
 */
interface AuthorizationDecisionMakerInterface extends MessageAuthorizerInterface
{
    /**
     * Adds an authorizer to this decision maker.
     */
    public function addAuthorizer(MessageAuthorizerInterface $authorizer): void;
}
