<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1\ServiceCheck;

class HealthCheckResponseView
{
    /** @var int */
    public $statusCode;

    /** @var array */
    public $headers;

    /** @var array */
    public $payload;

    public function __construct(?string $statusCode, ?array $headers, ?array $payload)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->payload = $payload;
    }
}
