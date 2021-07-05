<?php

namespace Morebec\Orkestra\Messaging\Timeout;

use Morebec\Orkestra\DateTime\DateTime;

abstract class AbstractTimeout implements TimeoutInterface
{
    public string $id;

    public DateTime $endsAt;

    public function __construct(string $id, DateTime $endsAt)
    {
        $this->id = $id;
        $this->endsAt = $endsAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEndsAt(): DateTime
    {
        return $this->endsAt;
    }
}
