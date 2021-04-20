<?php

namespace Morebec\Orkestra\Messaging\Authorization;

use Morebec\Orkestra\Messaging\AbstractMessageBusResponse;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;

/**
 * Represents a Response of Not Authorizated.
 */
class UnauthorizedResponse extends AbstractMessageBusResponse implements MessageBusResponseInterface
{
    public function __construct(UnauthorizedException $exception)
    {
        parent::__construct(MessageBusResponseStatusCode::REFUSED(), $exception);
    }
}
