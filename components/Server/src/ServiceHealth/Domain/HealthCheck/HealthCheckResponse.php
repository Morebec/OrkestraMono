<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck;

use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthStatus;

/**
 * Represents the response of a health check.
 */
class HealthCheckResponse
{
    /**
     * @var HealthStatus
     */
    private $status;

    /**
     * @var int|null
     */
    private $statusCode;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var array|null
     */
    private $payload;

    /**
     * @var bool
     */
    private $timeout;

    private function __construct(
        HealthStatus $status,
        ?int $statusCode,
        array $headers,
        ?array $payload,
        bool $timeout
    ) {
        $this->status = $status;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->payload = $payload;
        $this->timeout = $timeout;
    }

    /**
     * Creates a new response.
     *
     * @return static
     */
    public static function create(HealthStatus $status, int $statusCode, array $headers, ?array $payload): self
    {
        return new self($status, $statusCode, $headers, $payload, false);
    }

    /**
     * Creates a response representing a timeout.
     *
     * @return static
     */
    public static function timeout(): self
    {
        return new self(HealthStatus ::UNHEALTHY(), null, [], null, true);
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    public function getStatus(): HealthStatus
    {
        return $this->status;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Indicates if this response represents a timeout.
     */
    public function isTimeout(): bool
    {
        return $this->timeout;
    }
}
