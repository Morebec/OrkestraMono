<?php

namespace Morebec\Orkestra\EventSourcing\Snapshot;

use Morebec\Orkestra\EventSourcing\EventStore\EventStreamId;

interface StreamIdToAggregateClassNameResolverInterface
{
    /**
     * Resolves an event stream ID to a given Aggregate Class Name for snapshotting.
     */
    public function resolve(EventStreamId $streamId): string;
}
