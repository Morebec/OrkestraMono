<?php

namespace Morebec\Orkestra\Privacy;

use Morebec\Orkestra\DateTime\DateTime;

/**
 * Interface relating to personal information that was recorded in the {@link PersonalInformationStoreInterface}.
 */
interface RecordedPersonalDataInterface extends PersonalDataInterface
{
    /**
     * Returns a reference to this personal data in the store.
     * This reference token is globally unique.
     */
    public function getReferenceToken(): string;

    /**
     * Indicates the date at which this information was stored.
     * If this is not provided the store will set this information on the date at which it receives it the first time.
     */
    public function getRecordedAt(): DateTime;
}
