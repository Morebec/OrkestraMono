<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Infrastructure;

use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheck;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckResponse;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthCheck\HealthCheckRunnerInterface;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\HealthStatus;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\ServiceCheck;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpHealthCheckRunner implements HealthCheckRunnerInterface
{
    /** @var int Used for degraded */
    public const DEGRADED_RESPONSE_CODE = 199;

    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function runCheck(HealthCheck $healthCheck, ServiceCheck $serviceCheck): HealthCheckResponse
    {
        try {
            $response = $this->client->request(Request::METHOD_GET, $healthCheck->getUrl(), [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => $healthCheck->getTimeout()->toInt(),
                'query' => [
                    'serviceCheckId' => (string) $serviceCheck->getId(),
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $headers = $response->getHeaders(false);
            try {
                $payload = $response->toArray(false);
            } catch (DecodingExceptionInterface $e) {
                $payload = ['errorMessage' => $e->getMessage()];
            }

            if ($statusCode === Response::HTTP_OK) {
                $status = HealthStatus::HEALTHY();
            } elseif ($statusCode === self::DEGRADED_RESPONSE_CODE) {
                $status = HealthStatus::DEGRADED();
            } else {
                $status = HealthStatus::UNHEALTHY();
            }

            return HealthCheckResponse::create($status, $statusCode, $headers, $payload);
        } catch (TransportExceptionInterface $e) {
            if ($e instanceof TimeoutExceptionInterface) {
                return HealthCheckResponse::timeout();
            }

            if ($e instanceof TransportException) {
                return HealthCheckResponse::timeout();
            }

            throw $e;
        }
    }
}
