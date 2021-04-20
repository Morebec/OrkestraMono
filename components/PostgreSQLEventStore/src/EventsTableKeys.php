<?php

namespace Morebec\Orkestra\PostgreSqlEventStore;

/** @internal  */
final class EventsTableKeys
{
    public const ID = 'id';
    public const STREAM_ID = 'stream_id';
    public const STREAM_VERSION = 'stream_version';
    public const TYPE = 'type';
    public const METADATA = 'metadata';
    public const DATA = 'data';
    public const RECORDED_AT = 'recorded_at';
    public const SEQUENCE_NUMBER = 'sequence_number';

    private function __construct()
    {
    }
}
