<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

/**
 * This normalizer uses multiple other normalizes, in order to be able to cover a wide range of cases.
 * It also supports registering new custom normalizers.
 */
class Normalizer implements NormalizerInterface
{
    /** @var NormalizerInterface[] */
    private $builtInNormalizers;

    /**
     * @var NormalizerInterface[]
     */
    private $normalizers;

    public function __construct()
    {
        $this->normalizers = [];
        $this->builtInNormalizers = [
            NullValueNormalizer::class => new NullValueNormalizer(),
            ScalarValueNormalizer::class => new ScalarValueNormalizer(),
            ThrowableNormalizer::class => new ThrowableNormalizer(),
            TraversableNormalizer::class => new TraversableNormalizer($this),
            ArrayNormalizer::class => new ArrayNormalizer($this),

            DateTimeNormalizer::class => new DateTimeNormalizer(),

            ObjectReflectionNormalizer::class => new ObjectReflectionNormalizer($this),
        ];
    }

    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }

        // Check with Custom Normalizers.
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supports($context)) {
                return $normalizer->normalize($context);
            }
        }

        // Check with builtin normalizers.
        foreach ($this->builtInNormalizers as $normalizer) {
            if ($normalizer->supports($context)) {
                return $normalizer->normalize($context);
            }
        }

        throw new UnsupportedNormalizerValueException($context, $this);
    }

    public function supports(NormalizationContextInterface $context): bool
    {
        // Check with Custom Normalizers.
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supports($context)) {
                return true;
            }
        }

        // Check with builtin normalizers.
        foreach ($this->builtInNormalizers as $normalizer) {
            if ($normalizer->supports($context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a normalizer to the list of normalizers.
     */
    public function addNormalizer(NormalizerInterface $normalizer): void
    {
        if ($normalizer instanceof DelegatingNormalizerInterface) {
            if (!$normalizer->getDelegate()) {
                $normalizer->setDelegate($this);
            }
        }

        // Internally this is considered non built in normalizers.
        $this->normalizers[] = $normalizer;
    }
}
