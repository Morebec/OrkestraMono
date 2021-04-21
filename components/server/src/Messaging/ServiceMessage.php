<?php

namespace Morebec\Orkestra\OrkestraServer\Messaging;

class ServiceMessage
{
    /**
     * ID of the service that sent this message to the current server.
     *
     * @var ServiceId
     */
    private $serviceId;

    /**
     * Type of the message E.g. COMMAND, QUERY, EVENT.
     *
     * @var MessageType
     */
    private $type;

    /**
     * @var MessageTypeName
     */
    private $typeName;

    /**
     * @var MessagePayload
     */
    private $payload;

    /**
     * @var ServiceMessageHeaders
     */
    private $headers;

    /**
     * ServiceMessage constructor.
     */
    public function __construct(
        ServiceId $serviceId,
        MessageType $type,
        MessageTypeName $typeName,
        MessagePayload $payload,
        ServiceMessageHeaders $headers
    ) {
        $this->serviceId = $serviceId;
        $this->type = $type;
        $this->typeName = $typeName;
        $this->payload = $payload;
        $this->headers = $headers;
    }

    public function getServiceId(): ServiceId
    {
        return $this->serviceId;
    }

    public function getHeaders(): ServiceMessageHeaders
    {
        return $this->headers;
    }

    public function getPayload(): MessagePayload
    {
        return $this->payload;
    }

    public function getType(): MessageType
    {
        return $this->type;
    }

    public function getTypeName(): MessageTypeName
    {
        return $this->typeName;
    }
}
