<?php

namespace Morebec\Orkestra\OrkestraServer\Messaging;

interface ServiceRepositoryInterface
{
    public function findById(ServiceId $serviceId): void;
}
