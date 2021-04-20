<?php

namespace Morebec\Orkestra\Messaging;

/**
 * Message headers represent metadata about a message when going through the message bus.
 * The data contained in these headers should always be primitive scalar types or null with the exception of arrays
 * that can also be stored, as long as it contains only the aforementioned value types.
 *
 * This class contains constant for common types of metadata.
 */
class MessageHeaders
{
    /**
     * Key in the headers representing the ID of the message.
     * Expected Value: string|null.
     *
     * @var string
     */
    public const MESSAGE_ID = 'messageId';

    /**
     * Key in the headers representing the type name of the message.
     * Expected Value: string|null.
     *
     * @var string
     */
    public const MESSAGE_TYPE_NAME = 'messageTypeName';

    /**
     * Key in the headers representing the type of the message. (e.g. COMMAND, EVENT, QUERY, GENERIC, TECHNICAL_EVENT, etc.).
     * Expected Value: string|null.
     *
     * @var string
     */
    public const MESSAGE_TYPE = 'messageType';

    /**
     * Key in the headers representing the destination handler where this message should be sent.
     * This header is optional and can support a null value (null or empty array) or an array.
     * In that case, the message will be sent to all subscribed handlers.
     * Otherwise it can be used to force specific handlers to receive a given message.
     * Expected Value: string[]|null.
     *
     * Each string should be as follows:
     * - handlerClassName::methodName.
     *
     * This is used by the {@link HandleMessageMiddleware} and the {@link RouteMessageMiddleware}
     *
     * @var string
     */
    public const DESTINATION_HANDLER_NAMES = 'destinationHandlerNames';

    /**
     * Key in the headers representing the datetime at which this message was sent.
     * Expected Value: milliseconds precise timestamp eg. 12456879.25 (U.u format) or null.
     *
     * @var float
     */
    public const SENT_AT = 'sentAt';

    /**
     * Key in the headers representing the datetime at which this message is scheduled to be sent.
     * Expected Value: milliseconds precise timestamp|null.
     *
     * @var string
     */
    public const SCHEDULED_AT = 'scheduledAt';

    /**
     * Key in the headers representing the ID of a tenant to which this message is belongs or is directed.
     * Expected Value: string|null.
     *
     * @var string
     */
    public const TENANT_ID = 'tenantId';

    /**
     * Key in the headers representing the ID of a client application from which this message originates. (e.g. RestApi:v1, AndroidApp:v2.56)
     * Expected Value: string|null.
     *
     * @var string
     */
    public const APPLICATION_ID = 'applicationId';

    /**
     * Key in the headers representing the ID of a tenant to which this message is belongs or is directed.
     * Expected Value: string|null.
     *
     * @var string
     */
    public const USER_ID = 'userId';

    /**
     * Key in the headers representing the correlation ID.
     * The correlation ID is used to track the initial message that was responsible for this message to be sent at
     * a given point.
     * Expected Value: string.
     *
     * @var string
     */
    public const CORRELATION_ID = 'correlationId';

    /**
     * Key in the headers representing the causation ID.
     * The causation ID is used to track the message that caused this message to be sent.
     * By usually following the chain of causation ids of a given message we land on the correlation message.
     * Expected Value: string|null.
     *
     * @var string
     */
    public const CAUSATION_ID = 'causationId';

    /**
     * @var array
     */
    protected $values;

    public function __construct(array $values = [])
    {
        $this->values = [];

        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Adds a Header information.
     */
    public function set(string $key, $value): void
    {
        $this->values[$key] = $value;
    }

    /**
     * Returns the value of a key, or a default value.
     *
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function get(string $key, $defaultValue = null)
    {
        if (!$this->has($key)) {
            return $defaultValue;
        }

        return $this->values[$key];
    }

    /**
     * Indicates if a given key is present.
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->values);
    }

    /**
     * Removes a key from the headers.
     */
    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($this->values[$key]);
        }
    }

    /**
     * Returns an array representation of the headers.
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * Returns a copy of this message headers.
     */
    public function copy(): self
    {
        return new self($this->values);
    }
}
