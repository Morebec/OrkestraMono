<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1;

use Morebec\Orkestra\Messaging\MessageHeaders;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QueryController extends AbstractApiController
{
    /**
     * @Route(
     *     name="api.query.execute",
     *     path="/query/{queryTypeName}",
     *     methods={"POST"}
     * )
     *
     * @throws InvalidApiRequestException
     */
    public function __invoke(Request $request, string $queryTypeName): JsonResponse
    {
        // TODO Authenticate call.
        try {
            $query = $this->messageNormalizer->denormalize($request->request->all(), $queryTypeName);
        } catch (\Throwable $throwable) {
            return $this->json([
                'status' => 'failed',
                'error' => \get_class($throwable),
                'message' => $throwable->getMessage(),
                'data' => null,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $response = $this->messageBus->sendMessage($query, new MessageHeaders([
            // TODO detect service related data from request.
        ]));

        $this->createResponse($query, $response);
    }
}
