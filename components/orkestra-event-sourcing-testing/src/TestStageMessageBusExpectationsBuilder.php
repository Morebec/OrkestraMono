<?php

namespace Morebec\Orkestra\EventSourcing\Testing;

use Morebec\Orkestra\EventSourcing\Testing\Expectation\MessageBusShouldRespondAs;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\MessageBusShouldRespondWithPayload;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\MessageBusShouldRespondWithStatusCode;
use Morebec\Orkestra\EventSourcing\Testing\Expectation\MessageBusShouldResponseWithUuidV4AsPayload;
use Morebec\Orkestra\Messaging\MessageBusResponseStatusCode;

class TestStageMessageBusExpectationsBuilder extends TestStageExpectationsBuilder
{
    public function then(): self
    {
        return $this;
    }

    /**
     * Ads an expectation that the message bus should have responded with requirements from
     * as defined by the given function.
     *
     * @param $func
     *
     * @return $this
     */
    public function messageBusShouldRespondAs($func): self
    {
        return $this->expect(new MessageBusShouldRespondAs($func));
    }

    /**
     * Adds an expectation that the message bus should have responded with a given payload.
     *
     * @param $payload
     *
     * @return $this
     */
    public function messageBusShouldRespondWithPayload($payload): self
    {
        return $this->expect(new MessageBusShouldRespondWithPayload($payload));
    }

    /**
     * Adds an expectation that the message bus should have responded with null as its payload.
     *
     * @return $this
     */
    public function messageBusShouldRespondWithNullPayload(): self
    {
        return $this->messageBusShouldRespondWithPayload(null);
    }

    /**
     * Adds an expectation that the message bus should have responded with a UuidV4 string as a payload.
     *
     * @return $this
     */
    public function messageBusShouldRespondWithUuidV4AsPayload(): self
    {
        return $this->expect(new MessageBusShouldResponseWithUuidV4AsPayload());
    }

    /**
     * Adds an expectation that the message bus should have responded with a given status.
     *
     * @param $code
     *
     * @return $this
     */
    public function messageBusShouldRespondWithStatusCode($code): self
    {
        if (\is_string($code)) {
            $code = MessageBusResponseStatusCode::fromString($code);
        }

        return $this->expect(new MessageBusShouldRespondWithStatusCode($code));
    }

    /**
     * Adds an expectation that the message bus should have responded with a status code of self::SUCCEEDED().
     *
     * @return $this
     */
    public function messageBusShouldRespondWithStatusCodeSucceeded(): self
    {
        return $this->messageBusShouldRespondWithStatusCode(MessageBusResponseStatusCode::SUCCEEDED());
    }

    /**
     * Adds an expectation that the message bus should have responded with a status code of self::ACCEPTED().
     *
     * @return $this
     */
    public function messageBusShouldRespondWithStatusCodeAccepted(): self
    {
        return $this->messageBusShouldRespondWithStatusCode(MessageBusResponseStatusCode::ACCEPTED());
    }

    /**
     * Adds an expectation that the message bus should have responded with a status code of SKIPPED.
     *
     * @return $this
     */
    public function messageBusShouldRespondWithStatusCodeSkipped(): self
    {
        return $this->messageBusShouldRespondWithStatusCode(MessageBusResponseStatusCode::SKIPPED());
    }

    /**
     * Adds an expectation that the message bus should have responded with a status code of INVALID.
     *
     * @return $this
     */
    public function messageBusShouldRespondWithStatusCodeInvalid(): self
    {
        return $this->messageBusShouldRespondWithStatusCode(MessageBusResponseStatusCode::INVALID());
    }

    /**
     * Adds an expectation that the message bus should have responded with a status code of REFUSED.
     *
     * @return $this
     */
    public function messageBusShouldRespondWithStatusCodeRefused(): self
    {
        return $this->messageBusShouldRespondWithStatusCode(MessageBusResponseStatusCode::REFUSED());
    }

    /**
     * Adds an expectation that the message bus should have responded with a status code of FAILED.
     *
     * @return $this
     */
    public function messageBusShouldRespondWithStatusCodeFailed(): self
    {
        return $this->messageBusShouldRespondWithStatusCode(MessageBusResponseStatusCode::FAILED());
    }
}
