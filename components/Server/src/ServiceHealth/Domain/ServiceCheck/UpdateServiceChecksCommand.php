<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceHealth\Domain\ServiceCheck;

use Morebec\Orkestra\Messaging\Domain\Command\DomainCommandInterface;

class UpdateServiceChecksCommand implements DomainCommandInterface
{
    /** @var string */
    public $serviceId;

    /** @var ServiceCheckCommandDto[] */
    public $serviceChecks = [];

    public static function getTypeName(): string
    {
        return 'service.health_checking.update_check_definitions';
    }
}
