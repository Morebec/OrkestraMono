<?php

namespace Morebec\Orkestra\Framework\Messaging;

use Psr\Log\LoggerInterface;

class LoggerMessageAuditEventStorage implements MessageAuditEventStorageInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function add(MessageAuditEvent $event): void
    {
        $this->logger->info(
            'Message Audit: {type} {messageTypeName}',
            (array) $event
        );
    }
}
