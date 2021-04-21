<?php

namespace Morebec\Orkestra\DateTime;

/**
 * Interface for an application clock.
 * It provides the current time to the whole application.
 */
interface ClockInterface
{
    /**
     * Returns today's date.
     */
    public function today(): Date;

    /**
     * Returns yesterday's date.
     */
    public function yesterday(): Date;

    /**
     * Returns tomorrow's date.
     */
    public function tomorrow(): Date;

    /**
     * Returns the current date and time.
     */
    public function now(): DateTime;
}
