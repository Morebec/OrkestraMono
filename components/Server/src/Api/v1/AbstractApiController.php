<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1;

use Morebec\Orkestra\Messaging\MessageBusInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseInterface;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;
use Morebec\Orkestra\Messaging\MessageInterface;
use Morebec\Orkestra\Messaging\Normalization\MessageNormalizerInterface;
use Morebec\Orkestra\Normalization\ObjectNormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractApiController extends AbstractController
{
    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * @var MessageNormalizerInterface
     */
    protected $messageNormalizer;
    /**
     * @var ObjectNormalizerInterface
     */
    private $objectNormalizer;

    public function __construct(
        MessageBusInterface $messageBus,
        MessageNormalizerInterface $messageNormalizer,
        ObjectNormalizerInterface $objectNormalizer
    ) {
        $this->messageBus = $messageBus;
        $this->messageNormalizer = $messageNormalizer;
        $this->objectNormalizer = $objectNormalizer;
    }

    protected function createResponse(?MessageInterface $message, MessageBusResponseInterface $messageBusResponse): JsonResponse
    {
        if (!$messageBusResponse->isSuccess()) {
            return $this->createFailureResponse($message, $messageBusResponse);
        }

        return $this->createSuccessResponse($message, $messageBusResponse);
    }

    protected function createSuccessResponse(?MessageInterface $command, MessageBusResponseInterface $response): JsonResponse
    {
        $payload = $response->getPayload();

        if ($response->getStatusCode()->isEqualTo(MessageBusResponseStatusCode::ACCEPTED())) {
            $httpStatusCode = JsonResponse::HTTP_ACCEPTED;
        }/* elseif (!$payload) {
            $httpStatusCode = JsonResponse::HTTP_NO_CONTENT;
        }*/ else {
            $httpStatusCode = JsonResponse::HTTP_OK;
        }

        return $this->json([
            'status' => 'succeeded',
            'data' => $this->objectNormalizer->normalize($payload),
        ], $httpStatusCode);
    }

    protected function createFailureResponse(?MessageInterface $message, MessageBusResponseInterface $messageBusResponse): JsonResponse
    {
        $payload = $messageBusResponse->getPayload();

        if ($messageBusResponse->getStatusCode()->isEqualTo(MessageBusResponseStatusCode::INVALID())) {
            $httpStatusCode = JsonResponse::HTTP_BAD_REQUEST;
        } elseif ($messageBusResponse->getStatusCode()->isEqualTo(MessageBusResponseStatusCode::FAILED())) {
            $httpStatusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        } elseif ($messageBusResponse->getStatusCode()->isEqualTo(MessageBusResponseStatusCode::REFUSED())) {
            $httpStatusCode = JsonResponse::HTTP_FORBIDDEN;
        } else {
            $httpStatusCode = JsonResponse::HTTP_BAD_REQUEST;
        }

        if ($payload instanceof \Throwable) {
            $apiErrorMessage = $payload->getMessage();
            $apiErrorType = (new \ReflectionClass($payload))->getShortName();
            if ($payload instanceof \InvalidArgumentException) {
                $httpStatusCode = JsonResponse::HTTP_BAD_REQUEST;
            }
            /* elseif ($payload instanceof NotFoundExceptionInterface) {
                $apiErrorType = 'NotFoundException';
                $httpStatusCode = JsonResponse::HTTP_NOT_FOUND;
            }*/
        } else {
            $apiErrorMessage = 'There was an error processing your request.';
            $apiErrorType = 'unprocessed_request';
        }

        return $this->json([
            'status' => 'failed',
            'error' => $apiErrorType,
            'message' => $apiErrorMessage,
            'data' => null,
        ], $httpStatusCode);
    }
}
