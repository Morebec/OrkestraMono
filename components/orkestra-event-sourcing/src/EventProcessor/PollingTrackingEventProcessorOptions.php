<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

class PollingTrackingEventProcessorOptions extends TrackingEventProcessorOptions
{
    /**
     * Delay between polls in milliseconds.
     */
    public int $pollingDelay = 10;
}
