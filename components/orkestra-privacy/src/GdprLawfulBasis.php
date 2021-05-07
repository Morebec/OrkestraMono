<?php

namespace Morebec\Orkestra\Privacy;

/**
 * List of lawful basis for processing personal data.
 * These values can be used as Processing Requirements.
 */
class GdprLawfulBasis
{
    /**
     * Indicates the the user has given consent for data processing.
     */
    public const USER_CONSENT = 'USER_CONSENT';

    /**
     * Represents contractual information. I.e. the data needs to be processed
     * as part of a contract.
     */
    public const CONTRACT = 'CONTRACT';

    /**
     * This is used for legal obligations such as governmental requirements (e.g. finances).
     */
    public const LEGAL_REQUIREMENT = 'LEGAL_REQUIREMENT';

    /**
     * For legitimate interest, that is your organization has a legitimate interest for processing the data.
     * It is the responsibility of the organization to define and prove the legitimate interest.
     * E.g.: Fraud prevention, marketing, research, commercial interests, IT security,.
     */
    public const LEGITIMATE_INTEREST = 'LEGITIMATE_INTEREST';

    /**
     * Indicates that the processing is required in order to protect someone's life.
     */
    public const VITAL_INTEREST = 'VITAL_INTEREST';

    /**
     * For public interest.
     */
    public const PUBLIC_INTEREST = 'PUBLIC_INTEREST';
}
