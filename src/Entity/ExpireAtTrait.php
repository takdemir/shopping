<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


trait ExpireAtTrait
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected ?DateTime $expireAt;

    public function getExpireAt(): ?DateTime
    {
        return $this->expireAt;
    }

    public function setExpireAt(?DateTime $expireAt): void
    {
        if ($expireAt) {
            $this->expireAt = $expireAt;
        }
    }
}
