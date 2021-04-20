<?php

namespace Morebec\Orkestra\EventSourcing\Upcasting;

/**
 * Allows to pass an event descriptor through a series of upcasters.
 * It works like a pipe passing the result of the previous upcaster to the next one.
 */
class UpcasterChain implements UpcasterInterface
{
    /**
     * @var array
     */
    private $upcasters;

    public function __construct(iterable $upcasters)
    {
        $this->upcasters = [];
        foreach ($upcasters as $upcaster) {
            $this->addUpcaster($upcaster);
        }
    }

    public function upcast(UpcastableEventDescriptor $eventDescriptor): array
    {
        return $this->doUpcast($this->upcasters, $eventDescriptor);
    }

    public function supports(UpcastableEventDescriptor $eventDescriptor): bool
    {
        foreach ($this->upcasters as $upcaster) {
            if ($upcaster->supports($eventDescriptor)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Indicates if this chain is empty or not.
     */
    public function isEmpty(): bool
    {
        return empty($this->upcasters);
    }

    /**
     * Adds an upcaster to this chain.
     */
    public function addUpcaster(UpcasterInterface $upcaster): void
    {
        $this->upcasters[] = $upcaster;
    }

    private function doUpcast(array $chain, UpcastableEventDescriptor $event): array
    {
        if (empty($chain)) {
            return [$event];
        }

        $head = \array_slice($chain, 0, 1);
        $tail = \array_slice($chain, 1);

        $events = $head[0]->upcast($event);

        $result = [];
        foreach ($events as $key => $msg) {
            $result = array_merge($result, $this->doUpcast($tail, $msg));
        }

        return $result;
    }
}
