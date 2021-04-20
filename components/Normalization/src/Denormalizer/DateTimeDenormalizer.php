<?php

namespace Morebec\Orkestra\Normalization\Denormalizer;

use DateTimeInterface;
use Morebec\Orkestra\DateTime\Date;
use Morebec\Orkestra\DateTime\DateTime;

class DateTimeDenormalizer implements DenormalizerInterface
{
    public function denormalize(DenormalizationContextInterface $context)
    {
        if (!$this->supports($context)) {
            throw new UnsupportedDenormalizerValueException($context, $this);
        }

        $value = $context->getValue();

        if ($context->getTypeName() === Date::class) {
            try {
                // Try by default RFC3339_EXTENDED
                return Date::createFromFormat(Date::RFC3339_EXTENDED, $value);
            } catch (\InvalidArgumentException $exception) {
                // In case of failure try the generic date parser that might support things like YYYY-MM-DD or YYYY/MM/DD
                return new Date($value);
            }
        }

        return DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $value);
    }

    public function supports(DenormalizationContextInterface $context): bool
    {
        $typeName = $context->getTypeName();

        return $typeName === DateTime::class || $typeName === Date::class || $typeName === DateTimeInterface::class;
    }
}
