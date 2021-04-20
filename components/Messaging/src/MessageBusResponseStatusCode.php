<?php

namespace Morebec\Orkestra\Messaging;

use Morebec\Orkestra\Enum\Enum;

/**
 * Represents a status code accompanying a {@link MessageBusResponseInterface} .
 *
 * @method static self ACCEPTED()
 * @method static self SUCCEEDED()
 * @method static self SKIPPED()
 * @method static self REFUSED()
 * @method static self INVALID()
 * @method static self FAILED()
 */
class MessageBusResponseStatusCode extends Enum
{
    /**
     * Indicates that the request has been accepted for processing, but the processing
     * cannot be completed right now and needs asynchronous work.
     * Such a request SHOULD include a response message that can serve as an indication of the request's current status
     * by the use of an Identifier that can serve as a pointer for progress monitoring.
     * NOTE: This is considered as a successful Status Code.
     */
    public const ACCEPTED = 'ACCEPTED';

    /**
     * The request has succeeded. The message returned with the response will be highly dependent on the request's
     * message type and message name:
     * - Queries: Will usually return a message corresponding to the requested information.
     * - Command: Will usually return nothing.
     * NOTE: This is considered as a successful Status Code.
     */
    public const SUCCEEDED = 'SUCCEEDED';

    /**
     * This status code indicates that the request was skipped as deemed unnecessary to be processed by the responding handler.
     * This can be returned in cases where for example a user wants to change their email address to the same one they already had.
     * NOTE: This is considered as a successful Status Code.
     */
    public const SKIPPED = 'SKIPPED';

    /**
     * Indicates that the handling of the request was explicitly refused due to an exception thrown because an invariant
     * has been violated.
     * The accompanying payload should be a {@link \Throwable} object.
     * NOTE: This is considered as a failure Status Code.
     */
    public const REFUSED = 'REFUSED';

    /**
     * This status code indicates that the request contains invalid data and cannot be processed because of this.
     * This is not to be confused with the REFUSED status code which is used for violated business invariants.
     * This status code indicates that PRIOR to executing the business logic, the message received contained invalid data.
     * An associated response message interface that explicitly describes the encountered validation errors should be provided.
     * NOTE: This is considered as a failure Status Code.
     */
    public const INVALID = 'INVALID';

    /**
     * This status code indicates that an UNEXPECTED exception was encountered while processing the request.
     * This type of exception SHOULD be seldom encountered. They usually indicate underlying missing catch
     * clauses or compensation actions. They are indicators of potential bugs.
     * The accompanying payload should be a {@link \Throwable} object.
     * NOTE: This is considered as a failure Status Code.
     */
    public const FAILED = 'FAILED';

    /**
     * Creates an instance of this class from a string representation.
     */
    public static function fromString(string $statusCode): self
    {
        return new self($statusCode);
    }
}
