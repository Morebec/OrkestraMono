<?php

namespace Morebec\Orkestra\Messaging\Routing;

use Morebec\Orkestra\Messaging\AbstractMessageBusResponse;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;

/**
 * Response indicating that no Message Handler received a given Message.
 */
class UnhandledMessageResponse extends AbstractMessageBusResponse
{
    public function __construct()
    {
        parent::__construct(MessageBusResponseStatusCode::SKIPPED());
    }
}
