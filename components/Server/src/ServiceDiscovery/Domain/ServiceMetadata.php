<?php

namespace Morebec\Orkestra\OrkestraServer\ServiceDiscovery\Domain;

class ServiceMetadata
{
    /** @var mixed[] */
    private $data;

    private function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function fromArray(array $metadata): self
    {
        return new self($metadata);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
