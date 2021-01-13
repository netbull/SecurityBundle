<?php

namespace NetBull\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ban
 *
 * @ORM\Table(name="security_bans")
 * @ORM\Entity(repositoryClass="NetBull\SecurityBundle\Repository\BanRepository")
 */
class Ban extends BaseListing
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expireAt;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getExpireAt(): \DateTime
    {
        return $this->expireAt;
    }

    /**
     * @param \DateTime $expireAt
     */
    public function setExpireAt(\DateTime $expireAt): void
    {
        $this->expireAt = $expireAt;
    }

    ######################################################
    #                                                    #
    #                   Helper Methods                   #
    #                                                    #
    ######################################################
}
