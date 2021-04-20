<?php

namespace Morebec\Orkestra\Privacy;

/**
 * Service removing Personal data that is considered disposable.
 */
interface DisposedPersonalDataRemoverInterface
{
    /**
     * Remove all personal data that is considered disposable as of the current date and time.
     */
    public function run(): void;
}
