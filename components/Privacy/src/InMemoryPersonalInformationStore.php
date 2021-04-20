<?php

namespace Morebec\Orkestra\Privacy;

use Morebec\Orkestra\DateTime\ClockInterface;

class InMemoryPersonalInformationStore implements PersonalInformationStoreInterface
{
    /**
     * @var RecordedPersonalDataInterface[][]
     */
    private $personalTokens;

    /**
     * @var ClockInterface
     */
    private $clock;

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

        $referenceToken = uniqid('pii:');
        $recorded = new RecordedPersonalData(
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

        $this->personalTokens[$personalToken][$referenceToken] = $recorded;

        return $referenceToken;
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
            foreach ($personalToken as $data) {
                return $data;
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

    private function hasPersonalToken(string $personalToken): bool
    {
        return \array_key_exists($personalToken, $this->personalTokens);
    }
}
