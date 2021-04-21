<?php

namespace Morebec\Orkestra\Messaging\Timer;

use Morebec\Orkestra\DateTime\DateTime;

abstract class AbstractTimer implements TimerInterface
{
    /** @var string */
    public $id;

    /** @var DateTime */
    public $endsAt;

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
