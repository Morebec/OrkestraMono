<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1;

use Morebec\Orkestra\EventSourcing\Projection\ProjectorGroup;

class PostgreSqlProjectorGroup extends ProjectorGroup
{
    public function __construct(iterable $projectors)
    {
        parent::__construct('sql_projections', $projectors);
    }
}
