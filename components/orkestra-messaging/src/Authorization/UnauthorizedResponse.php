<?php

namespace Morebec\Orkestra\Messaging\Authorization;

use Morebec\Orkestra\Messaging\AbstractMessageBusResponse;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;

/**
 * Represents a Response of Not Authorized.
 */
class UnauthorizedResponse extends AbstractMessageBusResponse
{
    public function __construct(UnauthorizedException $exception)
    {
        parent::__construct(MessageBusResponseStatusCode::REFUSED(), $exception);
    }
}
