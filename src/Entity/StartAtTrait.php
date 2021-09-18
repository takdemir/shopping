<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


trait StartAtTrait
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected ?DateTime $startAt;

    public function getStartAt(): ?DateTime
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTime $startAt): void
    {
        if ($startAt) {
            $this->startAt = $startAt;
        }
    }
}
