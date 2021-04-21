<?php

namespace Morebec\Orkestra\Privacy;

use Morebec\Orkestra\DateTime\DateTime;

/**
 * Represents Personal Data to be saved in the store.
 */
class PersonalData implements PersonalDataInterface
{
    /**
     * The person that is the owner of the record.
     *
     * @var string
     */
    public $personalToken;

    /**
     * This can be used when required to query a record.
     *
     * @var string name of the type of value that is saved (e.g. emailAddress, phoneNumber, IP Address).
     */
    public $keyName;

    /** @var mixed value of the PII */
    public $value;

    /**
     * A business identification of a mean by which the personal information of a person or data subject was collected:
     * E.g.: A Product's Landing Page Contact Form, an External Organization etc, a user within the application.
     *
     * @var string
     */
    public $source;

    /**
     * A list of business identifications of reasons why this information is collected by the operating business.
     * (E.g. Marketing, CRM, Processing/Analytics).
     *
     * @var string[]
     */
    public $reasons = [];

    /**
     * A business identification of the ways in which this information is going to be processed.
     *
     * @var string[]
     */
    public $processingRequirements = [];

    /**
     * Date Time at which this information should be considered no longer needed and be automatically deleted.
     * If this value is null it is considered that it should be erased upon request or other manual events.
     *
     * This field can also be used to implement a reservation pattern for applications where this storage is not the same
     * as the one used for the related data.
     * E.g.:
     * 0. User registration process begins
     * 1. A recorded is entered in the PersonalInformationStore with `$disposedAt` in 15 minutes. (email address, fullname).
     * 2. The registration is tried in the application's database.
     * 2a. If the transaction is committed successfully, the data is marked `$disposedAt = null`
     * 2b If the transaction had a hard failure (service down, or unable to recover) -> the data will disappear automatically.
     *
     * @var DateTime|null
     */
    public $disposedAt;

    /**
     * This metadata should be used to track additional information related to this record, such as
     * - The 3rd parties involved with this PII that should be notified upon breaches
     * or invocation of the right to erasure.
     * - Legal basis for storing this data.
     * - Processing Operations
     * - Agreements - (e.g: user consent v2021-01-01).
     *
     * @var array
     */
    public $metadata = [];

    public function __construct(string $personalToken, string $keyName, $value, string $source)
    {
        $this->personalToken = $personalToken;
        $this->keyName = $keyName;
        $this->value = $value;
        $this->source = $source;
        $this->disposedAt = null;
        $this->reasons = [];
        $this->processingRequirements = [];
    }

    public function disposedAt(?DateTime $disposedAt): self
    {
        $this->disposedAt = $disposedAt;

        return $this;
    }

    public function reasons(array $reasons): self
    {
        foreach ($reasons as $reason) {
            if (!\is_string($reason)) {
                throw new \InvalidArgumentException(sprintf('Expected string got "%s".', get_debug_type($reason)));
            }
        }
        $this->reasons = $reasons;

        return $this;
    }

    public function processingRequirements(array $requirements): self
    {
        foreach ($requirements as $requirement) {
            if (!\is_string($requirement)) {
                throw new \InvalidArgumentException(sprintf('Expected string got "%s".', get_debug_type($requirement)));
            }
        }
        $this->processingRequirements = $requirements;

        return $this;
    }

    public function metadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getPersonalToken(): string
    {
        return $this->personalToken;
    }

    public function getKeyName(): string
    {
        return $this->keyName;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function getProcessingRequirements(): array
    {
        return $this->processingRequirements;
    }

    public function getDisposedAt(): ?DateTime
    {
        return $this->disposedAt;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getValue()
    {
        return $this->value;
    }
}
