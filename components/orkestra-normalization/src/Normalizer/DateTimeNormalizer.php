<?php

namespace Morebec\Orkestra\Normalization\Normalizer;

use DateTimeInterface;
use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\DateTime\DateTime;

/**
 * Normalizes dates to a ISO 8601 formatted string.
 */
class DateTimeNormalizer implements NormalizerInterface
{
    public const DATE_FORMAT = '';

    public function normalize(NormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedNormalizerValueException($context, $this);
        }

        /** @var DateTime $value */
        $value = $context->getValue();

        return $value->format(DateTimeInterface::RFC3339_EXTENDED);
    }

    public function supports(NormalizationContextInterface $context): bool
    {
        $value = $context->getValue();

        return $value instanceof DateTime || $value instanceof Date || $value instanceof DateTimeInterface;
    }
}
