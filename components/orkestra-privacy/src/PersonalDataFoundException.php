<?php

namespace Morebec\Orkestra\Privacy;

use Throwable;

class PersonalDataFoundException extends \RuntimeException implements PersonalInformationStoreExceptionInterface
{
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
