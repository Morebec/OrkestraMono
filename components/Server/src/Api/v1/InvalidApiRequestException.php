<?php

namespace Morebec\Orkestra\OrkestraServer\Api\v1;

use Morebec\Orkestra\OrkestraServer\Api\v1\v1\Throwable;

/**
 * Thrown when a n Api Request is invalid (Bad Request).
 */
class InvalidApiRequestException extends \RuntimeException
{
    /**
     * @var array
     */
    private $validationErrors;

    public function __construct(array $validationErrors, Throwable $previous = null)
    {
        parent::__construct('Bad Request', 0, $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
