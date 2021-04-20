<?php

namespace Morebec\Orkestra\Exceptions;

/**
 * Interface used to describe exceptions that are transient, i.e.
 * exceptions that were caused by work that when retried could potentially succeed, without
 * changing anything in the way the work is performed.
 * (E.g. network timeouts, power outages etc.).
 */
interface TransientExceptionInterface extends \Throwable
{
}
