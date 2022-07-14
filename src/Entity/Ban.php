<?php

namespace NetBull\SecurityBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="security_bans")
 * @ORM\Entity(repositoryClass="NetBull\SecurityBundle\Repository\BanRepository")
 */
class Ban extends BaseListing
{
    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $expireAt = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return DateTime|null
     */
    public function getExpireAt(): ?DateTime
    {
        return $this->expireAt;
    }

    /**
     * @param DateTime|null $expireAt
     * @return Ban
     */
    public function setExpireAt(?DateTime $expireAt): Ban
    {
        $this->expireAt = $expireAt;
        return $this;
    }
}
