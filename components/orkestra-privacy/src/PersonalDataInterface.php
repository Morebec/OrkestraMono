<?php

namespace Morebec\Orkestra\Privacy;

use Morebec\Orkestra\DateTime\DateTime;

/**
 * Represents a value of "Personally Identifiable Information" (PII) also referred to as "Personal Data" for a given person.
 * That might or not have been saved in the store.
 *
 * GDPR:
 * A few reminders about Personal Data under the GDPR:
 * - PII corresponds "to any information relating to an identified or identifiable natural person".
 * - The term "natural person" refers to human beings and not companies which fall under the "legal person" definition.
 *   It also refers to a person that is still "alive". Therefore deceased people do not fall under the GDPR in most cases.
 *
 * - The term "any information" corresponds to both objective information (Height, weight, gender, sexual sexual orientation)
 *   as well as subjective information (employment, marital status etc.). It also means that there is no specific format; Meaning that a picture,
 *   a video or audio all fall under the label "personal data"  .
 *
 * Note: The term business identification relates to any internal identifier that is used
 * to standardize and describe a given process.
 *
 * E.g.:
 * MARKETING_PROCESSING: Personally Identifiable Information that is stored under these terms are processed
 * in order to allow our marketing department to do their standard operations. It will be stored in a computer owned by the organization
 * as well as an AWS S3 storage. It will be stored and encrypted using these algorithm.
 * These could also refer more categories of lawful basis such as
 * - user_consent,
 * - contract,
 * - legal_requirement
 * - legitimate_interest
 * - vital_interest
 * - public_interest.
 *
 * There can be cases where an application will be saving PII that was entered by a given user
 * concerning other data subjects. (E.g. an application serving as a CRM for its users.).
 * In these cases, practical use of the personal data should include additional metadata
 * to handle these situations.
 * One strategy could be to use the source field by applying the the GUID of the user that collected this information.
 * However in that case, there is a chance that the user is that performed this action is considered the data controller
 * and therefore should take the necessary steps to delete the data. In the case of an application that serves this purpose
 * as a third party a way for destroying this data should also be done.
 *
 * Concerning metadata, it is advised to add data such as:
 * - Processing Operations
 * - Agreements - (user consent)
 * - Legal basis
 * - Sharing with 3rd parties.
 */
interface PersonalDataInterface
{
    /**
     * Personal token used to relate this information to a specific data subject or person.
     */
    public function getPersonalToken(): string;

    /**
     * This can be used when required to query data.
     *
     * @return string name of the type of value that is saved (e.g. emailAddress, phoneNumber, IP Address).
     */
    public function getKeyName(): string;

    /**
     * A business identification of a mean by which the personal information of a person or data subject was collected:
     * E.g.: A Product's Landing Page Contact Form, an External Organization etc.
     */
    public function getSource(): string;

    /**
     * A list of business identifications of reasons why this information is collected by the operating business.
     * (E.g. Marketing, CRM, Processing/Analytics).
     *
     * @return string[]
     */
    public function getReasons(): array;

    /**
     * A business identification of the ways in which this information is going to be processed.
     *
     * @return string[]
     */
    public function getProcessingRequirements(): array;

    /**
     * Date Time at which this information should be considered no longer needed and be automatically deleted.
     * If this value is null it is considered that it should be erased upon of request or other manual events.
     */
    public function getDisposedAt(): ?DateTime;

    /**
     * This metadata should be used to track additional information related to this data
     * the 3rd parties involved with this PII that should be notified upon breaches
     * or invocation of the right to erasure.
     *
     * @return mixed[]
     */
    public function getMetadata(): array;

    /**
     * Returns the value associated with this data.
     * This should only contain primitive scalar types or arrays.
     *
     * @return mixed
     */
    public function getValue();
}
