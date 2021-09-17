<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


trait CreatedAtTrait
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected DateTime $createdAt;

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        if ($createdAt) {
            $this->createdAt = $createdAt;
        }
    }
}
