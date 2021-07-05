<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\DateTime\DateTime;

class InMemoryTimeoutStorage implements TimeoutStorageInterface
{
    /** @var TimeoutWrapper[] */
    private array $wrappers;

    public function __construct()
    {
        $this->wrappers = [];
    }

    /**
     * {@inheritDoc}
     */
    public function add(TimeoutWrapper $wrapper): void
    {
        $this->wrappers[$wrapper->getId()] = $wrapper;
    }

    /**
     * {@inheritDoc}
     */
    public function findByEndsAtBefore(DateTime $dateTime): array
    {
        return array_filter($this->wrappers, static function (TimeoutWrapper $wrapper) use ($dateTime) {
            $endsAt = $wrapper->getTimeout()->getEndsAt();

            return $endsAt->isBefore($dateTime) || $endsAt->isSameDay($dateTime);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function findByEndsAtBetween(DateTime $from, DateTime $to): array
    {
        return array_filter($this->wrappers, static fn (TimeoutWrapper $wrapper) => $wrapper->getTimeout()->getEndsAt()->isBetween($from, $to));
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $timeoutId): void
    {
        unset($this->wrappers[$timeoutId]);
    }
}
