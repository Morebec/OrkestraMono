<?php

namespace Morebec\Orkestra\EventSourcing\EventProcessor;

class InMemoryEventStorePositionStorage implements EventStorePositionStorageInterface
{
    /**
     * @var ?int[]
     */
    private ?array $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $processorId, ?int $position): void
    {
        $this->data[$processorId] = $position;
    }

    /**
     * {@inheritDoc}
     */
    public function reset(string $processorId): void
    {
        $this->data[$processorId] = null;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $processorId): ?int
    {
        return $this->data[$processorId] ?? null;
    }
}
