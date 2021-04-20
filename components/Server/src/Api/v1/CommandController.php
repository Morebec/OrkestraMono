<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommandController extends AbstractApiController
{
    /**
     * @Route(
     *     name="api.command.execute",
     *     path="/command/{commandTypeName}",
     *     methods={"POST"},
     *     priority="0"
     * )
     *
     * @throws InvalidApiRequestException
     */
    public function __invoke(Request $request, string $commandTypeName): JsonResponse
    {
        // TODO Authenticate call.
        try {
            $command = $this->messageNormalizer->denormalize($request->request->all(), $commandTypeName);
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

        return $this->createResponse($command, $response);
    }
}
