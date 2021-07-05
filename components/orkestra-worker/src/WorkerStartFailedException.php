<?php

namespace Morebec\Orkestra\Worker;

/**
 * Thrown when a worker fails to start.
 */
class WorkerStartFailedException extends \RuntimeException implements WorkerExceptionInterface
{
}
