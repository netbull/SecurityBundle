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
    private $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expireAt;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getExpireAt(): DateTime
    {
        return $this->expireAt;
    }

    /**
     * @param DateTime $expireAt
     * @return Ban
     */
    public function setExpireAt(DateTime $expireAt): Ban
    {
        $this->expireAt = $expireAt;

        return $this;
    }
}
