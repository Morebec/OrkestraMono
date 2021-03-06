<?php

namespace Morebec\Orkestra\Privacy;

use Morebec\Orkestra\DateTime\ClockInterface;

class InMemoryPersonalInformationStore implements PersonalInformationStoreInterface
{
    /**
     * @var RecordedPersonalDataInterface[][]
     */
    private array $personalTokens;

    private ClockInterface $clock;

    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
        $this->personalTokens = [];
    }

    /**
     * {@inheritDoc}
     */
    public function put(PersonalDataInterface $data): string
    {
        $personalToken = $data->getPersonalToken();
        if (!$this->hasPersonalToken($personalToken)) {
            $this->personalTokens[$personalToken] = [];
        }

        $referenceToken = $data->getReferenceToken();

        if ($referenceToken === self::UNDEFINED_REFERENCE_TOKEN) {
            $referenceToken = uniqid("pii:$personalToken/", true);
        }

        if ($this->findOneByKeyName($personalToken, $data->getKeyName())) {
            throw new PersonalDataFoundException('Personal data already in store, use replace instead of put.');
        }

        $recorded = $this->convertPersonalTokenToRecordedToken($referenceToken, $data);

        $this->personalTokens[$personalToken][$referenceToken] = $recorded;

        return $referenceToken;
    }

    public function replace(string $referenceToken, PersonalDataInterface $data): void
    {
        if (!$this->findOneByReferenceToken($referenceToken)) {
            throw PersonalDataNotFoundException::forReferenceToken($referenceToken);
        }

        $recorded = $this->convertPersonalTokenToRecordedToken($referenceToken, $data);
        $this->personalTokens[$data->getPersonalToken()][$referenceToken] = $recorded;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneByKeyName(string $personalToken, string $keyName): ?RecordedPersonalDataInterface
    {
        if (!$this->hasPersonalToken($personalToken)) {
            return null;
        }

        $records = $this->personalTokens[$personalToken];
        foreach ($records as $record) {
            if ($record->getKeyName() === $keyName) {
                return $record;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function findByPersonalToken(string $personalToken): array
    {
        if (!$this->hasPersonalToken($personalToken)) {
            return [];
        }

        return $this->personalTokens[$personalToken];
    }

    public function findOneByReferenceToken(string $referenceToken): ?RecordedPersonalDataInterface
    {
        foreach ($this->personalTokens as $personalToken) {
            foreach ($personalToken as $refToken => $data) {
                if ($referenceToken === $refToken) {
                    return $data;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function removeByKeyName(string $personalToken, string $keyName): void
    {
        $data = $this->personalTokens[$personalToken];
        foreach ($data as $key => $datum) {
            if ($datum->getKeyName() === $keyName) {
                unset($this->personalTokens[$personalToken][$key]);
            }
        }
    }

    public function remove(string $referenceToken): void
    {
        foreach ($this->personalTokens as $personalToken => $entries) {
            if (\array_key_exists($referenceToken, $entries)) {
                unset($this->personalTokens[$personalToken][$referenceToken]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function erase(string $personalToken): void
    {
        unset($this->personalTokens[$personalToken]);
    }

    protected function convertPersonalTokenToRecordedToken(string $referenceToken, PersonalDataInterface $data): RecordedPersonalData
    {
        return new RecordedPersonalData(
            $data->getPersonalToken(),
            $referenceToken,
            $data->getKeyName(),
            $data->getValue(),
            $data->getSource(),
            $data->getReasons(),
            $data->getProcessingRequirements(),
            $data->getDisposedAt(),
            $data->getMetadata(),
            $this->clock->now()
        );
    }

    private function hasPersonalToken(string $personalToken): bool
    {
        return \array_key_exists($personalToken, $this->personalTokens);
    }
}
