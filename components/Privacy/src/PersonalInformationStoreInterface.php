<?php

namespace Morebec\Orkestra\Privacy;

/**
 * Represents a Storage that can be used to contain all Personally Identifiable Information
 * that the application tracks. To provide an easy way to maintain and get rid of it when required.
 * The use of this storage should be an explicit dependency in all {@link DomainMessageHandler} and `Domain Services`,
 * when Personally identifiable information is stored as Privacy Data Compliance should be an explicit business requirement.
 *
 * This storage interface works with some core concepts:
 * - Personal Data represents a PII value such as a person's name, email address, phone number, birthdate etc.
 * - Personal Token - represents a token that is used internally by the application to identify a given person. Such as an internal UUID, in other words the owner of Personal Data.
 *   For GDPR compliance this value should not be natural key and easily disposable, i.e. it should not be used to identify the natural person after an erasure of their data.
 * - A Reference Token - Represents a reference to some given Personal Data that can be used in the application to reference the data contained there.
 *
 * Implementations of this store should take the necessary precautions to encrypt the data.
 */
interface PersonalInformationStoreInterface
{
    /**
     * Allows to put Personal Data to this store, or overwrite an already existing one.
     * and returns a reference token representing this data.
     * Note: This method does not provide any idempotency guarantees meaning that
     * consecutive calls to this method with the same personal data, might return
     * different reference tokens.
     *
     * @return string reference token to the PersonalData (see {@link RecordedPersonalDataInterface}
     */
    public function put(PersonalDataInterface $data): string;

    /**
     * Finds some personal data by its key name and personal token combination or null if it was not found.
     */
    public function findOneByKeyName(string $personalToken, string $keyName): ?RecordedPersonalDataInterface;

    /**
     * Finds some personal data by its reference token.
     */
    public function findOneByReferenceToken(string $referenceToken): ?RecordedPersonalDataInterface;

    /**
     * Find multiple entries of personal data by a related personal token.
     *
     * @return RecordedPersonalDataInterface[]
     */
    public function findByPersonalToken(string $personalToken): array;

    /**
     * Removes a given record of personal data by its its key name and personal token combination.
     */
    public function removeByKeyName(string $personalToken, string $keyName): void;

    /**
     * Removes a given record of personal data by its its reference token combination.
     */
    public function remove(string $referenceToken): void;

    /**
     * Erase all personal data linked to a personal token.
     */
    public function erase(string $personalToken): void;
}
