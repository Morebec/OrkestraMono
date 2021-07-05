<?php

namespace Morebec\Orkestra\Messaging\Normalization;

class MessageClassMap implements MessageClassMapInterface
{
    /** @var string[] */
    private array $classMap;

    public function __construct(iterable $mappings = [])
    {
        $this->classMap = [];
        foreach ($mappings as $key => $item) {
            $this->addMapping($key, $item);
        }
    }

    public function addMapping(string $messageTypeName, string $messageClassName): void
    {
        $this->classMap[$messageTypeName] = $messageClassName;
    }

    public function getClassNameForMessageTypeName(string $messageTypeName): ?string
    {
        return $this->classMap[$messageTypeName] ?? null;
    }

    public function toArray(): array
    {
        return $this->classMap;
    }
}
