<?php

namespace Morebec\Orkestra\Messaging\Normalization;

class MessageClassMap implements MessageClassMapInterface
{
    /** @var string[] */
    private $classMap;

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
        if (!\array_key_exists($messageTypeName, $this->classMap)) {
            return null;
        }

        return $this->classMap[$messageTypeName];
    }

    public function toArray(): array
    {
        return $this->classMap;
    }
}
