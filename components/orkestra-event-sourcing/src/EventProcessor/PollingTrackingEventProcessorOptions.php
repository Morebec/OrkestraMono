<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

class PollingTrackingEventProcessorOptions extends TrackingEventProcessorOptions
{
    /**
     * Delay between polls in milliseconds.
     *
     * @var int
     */
    public $pollingDelay = 10;
}
