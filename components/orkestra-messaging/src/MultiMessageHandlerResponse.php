<?php

namespace Morebec\Orkestra\Messaging;

/**
 * Response representing the fact that multiple {@link MessageHandlerInterface} returned a response for a given {@link MessageInterface}.
 * The final status code of the response is determined by doing a specific election of the best candidate code:
 * - Failure codes always have precedence over success codes. Meaning if there is at least one failure code, the final response will be a failure code.
 * - If at least one response returns FAILED, the final status code is failed.
 * - Then, the most common code is used.
 */
class MultiMessageHandlerResponse extends AbstractMessageBusResponse
{
    /**
     * @var MessageHandlerResponse[]
     */
    private $handlerResponses;

    public function __construct(iterable $handlerResponses)
    {
        if (empty($handlerResponses)) {
            throw new \InvalidArgumentException('A MultiMessageHandlerResponse cannot receive an empty array of responses');
        }

        if (\count($handlerResponses) === 1) {
            throw new \InvalidArgumentException('A MultiMessageHandlerResponse must receive an array of responses of a length greater than 1');
        }

        $this->handlerResponses = [];
        foreach ($handlerResponses as $handlerResponse) {
            if (!$handlerResponse instanceof MessageHandlerResponse) {
                throw new \InvalidArgumentException(sprintf('A MultiMessageHandlerResponse can only accept responses of type "%s".', MessageHandlerResponse::class));
            }
            $this->handlerResponses[] = $handlerResponse;
        }

        // Determine the payloads of this response.
        $payloads = [];
        foreach ($this->handlerResponses as $handlerResponse) {
            $payloads[$handlerResponse->getHandlerName()] = $handlerResponse->getPayload();
        }

        parent::__construct($this->determineStatusCode(), $payloads);
    }

    /**
     * Indicates if at least one response contained in this response has a given status code.
     */
    public function hasResponseWithStatus(MessageBusResponseStatusCode $statusCode): bool
    {
        foreach ($this->handlerResponses as $response) {
            if ($response->getStatusCode()->isEqualTo($statusCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return MessageHandlerResponse[]
     */
    public function getHandlerResponses(): array
    {
        return $this->handlerResponses;
    }

    /**
     * Returns the status code of this response as a computed generalized status code.
     * The status codes can either be SUCCEEDED or FAILED.
     */
    public function getStatusCode(): MessageBusResponseStatusCode
    {
        return parent::getStatusCode(); // TODO: Change the autogenerated stub
    }

    /**
     * Returns an array representing all the payloads of the handlers where the keys are the handler names and the values
     * their respective payloads.
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    private function determineStatusCode(): MessageBusResponseStatusCode
    {
        if ($this->hasResponseWithStatus(MessageBusResponseStatusCode::FAILED())) {
            return MessageBusResponseStatusCode::FAILED();
        }

        // Count the codes
        $codes = [];
        foreach ($this->handlerResponses as $handlerResponse) {
            $code = (string) $handlerResponse->getStatusCode();
            if (!\array_key_exists($code, $codes)) {
                $codes[$code] = 0;
            }
            $codes[$code]++;
        }

        // If there is a failure code we will only care about failure codes, thus filter out success code to only keep failure codes.
        $hasFailed = $this->hasResponseWithStatus(MessageBusResponseStatusCode::INVALID()) ||
            $this->hasResponseWithStatus(MessageBusResponseStatusCode::REFUSED());

        if ($hasFailed) {
            $codes = array_filter($codes, static function (string $key) {
                return \in_array($key, [
                    MessageBusResponseStatusCode::FAILED,
                    MessageBusResponseStatusCode::INVALID,
                    MessageBusResponseStatusCode::REFUSED,
                ]);
            }, \ARRAY_FILTER_USE_KEY);
        }

        // Sort from most common to less common.
        arsort($codes, \SORT_NUMERIC);

        return MessageBusResponseStatusCode::fromString(array_key_first($codes));
    }
}