<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration\Messaging;

use Morebec\Orkestra\Messaging\Normalization\ClassMapMessageNormalizer;

class MessageNormalizerConfiguration
{
    /** @var string[] */
    public array $normalizers;

    /** @var string[] */
    public array $denormalizers;

    public ?string $implementationClassName;

    public function __construct()
    {
        $this->normalizers = [];
        $this->denormalizers = [];
        $this->implementationClassName = null;
    }

    public function default(): self
    {
        return (new self())->usingDefaultImplementation();
    }

    public function usingDefaultImplementation(): self
    {
        return $this->usingImplementation(ClassMapMessageNormalizer::class);
    }

    public function usingImplementation(string $className): self
    {
        $this->implementationClassName = $className;

        return $this;
    }

    /**
     * Configures the Message Normalizer to use a given normalizer.
     *
     * @return $this
     */
    public function withNormalizer(string $className): self
    {
        $this->normalizers[] = $className;

        return $this;
    }

    /**
     * Configures the message normalizer to use a given denormalizer.
     *
     * @return $this
     */
    public function withDenormalizer(string $className): self
    {
        $this->denormalizers[] = $className;

        return $this;
    }

    public function withNormalizationPair(string $normalizerClassName, string $denormalizerClassName): self
    {
        return $this->withNormalizer($normalizerClassName)->withDenormalizer($denormalizerClassName);
    }
}
