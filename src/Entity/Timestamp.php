<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\PrePersist;

trait Timestamp {

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePresist()
    {
        $this->createdAt = new \DateTime();
    }
}
