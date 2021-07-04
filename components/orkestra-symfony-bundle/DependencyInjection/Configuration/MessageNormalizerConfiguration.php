<?php

namespace Morebec\Orkestra\SymfonyBundle\DependencyInjection\Configuration;

use Morebec\Orkestra\Messaging\Normalization\ClassMapMessageNormalizer;

class MessageNormalizerConfiguration
{
    /** @var string[] */
    public $normalizers;

    /** @var string[] */
    public $denormalizers;

    /** @var string|null */
    public $implementationClassName;

    public function __construct()
    {
        $this->normalizers = [];
        $this->denormalizers = [];
        $this->implementationClassName = null;
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
