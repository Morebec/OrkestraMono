<?php

use Morebec\Orkestra\OrkestraServer\Api\v1\ApiV1ModuleConfigurator;
use Morebec\Orkestra\OrkestraServer\Core\DependencyInjection\CoreModuleConfigurator;
use Morebec\Orkestra\OrkestraServer\ServiceDiscovery\ServiceDiscoveryModuleConfigurator;
use Morebec\Orkestra\OrkestraServer\ServiceHealth\ServiceHealthModuleConfigurator;

return [
    CoreModuleConfigurator::class => ['all' => true],
    ServiceDiscoveryModuleConfigurator::class => ['all' => true],
    ServiceHealthModuleConfigurator::class => ['all' => true],
    ApiV1ModuleConfigurator::class => ['all' => true],
];
