<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1\Service;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Morebec\Orkestra\OrkestraServer\Api\v1\AbstractApiController;
use Morebec\Orkestra\OrkestraServer\Api\v1\InvalidApiRequestException;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain\RegisterService\RegisterServiceCommand;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck\UpdateServiceChecksCommand;
use Morebec\Orkestra\Retry\RetryStrategy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegisterServiceController extends AbstractApiController
{
    /**
     * @Route(
     *     name="api.services.register",
     *     path="/services/register",
     *     methods={"POST"},
     *     priority="0"
     * )
     *
     * @throws InvalidApiRequestException
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO Authenticate call.
        try {
            $data = $request->request->all();
            $command = $this->messageNormalizer->denormalize($data, RegisterServiceCommand::getTypeName());
        } catch (\Throwable $throwable) {
            return $this->json([
                'status' => 'failed',
                'error' => \get_class($throwable),
                'message' => $throwable->getMessage(),
                'data' => null,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $response = $this->messageBus->sendMessage($command, new MessageHeaders([
            // TODO detect service related data from request.
        ]));

        // Lets enable health checking on it,
        if ($response->isSuccess() && \array_key_exists('serviceChecks', $data)) {
            $updateChecksFun = function () use ($data) {
                $command = $this->messageNormalizer->denormalize(['serviceId' => $data['serviceId'], 'serviceChecks' => $data['serviceChecks']], UpdateServiceChecksCommand::getTypeName());
                $response = $this->messageBus->sendMessage($command);

                if ($response->isFailure()) {
                    throw $response->getPayload();
                }
            };
            RetryStrategy::create()
                ->maximumAttempts(5)
                ->useExponentialBackoff(1000)
                ->execute($updateChecksFun);
        }

        return $this->createResponse($command, $response);
    }
}
