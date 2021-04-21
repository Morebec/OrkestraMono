<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

use Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer\MalformedValueForTargetTypeException;

/**
 * Normalizes a scalar value.
 * This normalizer is quite simple, only ensuring the values received are scalar values and returning them
 * since they are already considered normalized.
 */
class ScalarValueDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize(DenormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedDenormalizerValueException($context, $this);
        }

        $value = $context->getValue();

        // Ensure we actually have a scalar
        if (!is_scalar($value)) {
            throw new MalformedValueForTargetTypeException('scalar', $context, $this);
        }

        // Return it as is, normalized forms only contain primitives.

        // Strict mode.
        $typeName = $context->getTypeName();
        if ($typeName !== 'scalar') {
            if (get_debug_type($value) !== $typeName) {
                throw new MalformedValueForTargetTypeException($typeName, $context, $this);
            }
        }

        // TODO CONFIGURATION OPTION FOR THIS.

        // Safe mode.
        if ($typeName === 'string') {
            return (string) $value;
        }

        if ($typeName === 'float') {
            return (float) $value;
        }

        if ($typeName === 'int') {
            return (int) $value;
        }

        if ($typeName === 'bool') {
            return (bool) $value;
        }

        return $value;
    }

    public function supports(DenormalizationContextInterface $context): bool
    {
        $scalarTypes = [
            'string',
            'bool',
            'int',
            'float',

            // Sometimes the following alternatives can be defined in phpDocs although not valid as per
            // https://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.var.pkg.html
            'double',
            'false',
            'true',
        ];

        return is_scalar($context->getValue()) || $context->getTypeName() === 'scalar' || \in_array($context->getTypeName(), $scalarTypes);
    }
}
