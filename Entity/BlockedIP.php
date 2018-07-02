<?php

namespace NetBull\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BlockedIP
 *
 * @ORM\Table(name="blocked_ips")
 * @ORM\Entity(repositoryClass="NetBull\SecurityBundle\Repository\BlockedIPRepository")
 */
class BlockedIP
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
     * @var string
     *
     * @Assert\Ip
     * @ORM\Column(name="ip", type="string", length=15)
     */
    private $ip;

    /**
     * @var int
     *
     * @ORM\Column(name="attempts", type="integer")
     */
    private $attempts = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_attempt_at", type="datetime")
     */
    private $lastAttemptAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="banned_at", type="datetime", nullable=true)
     */
    private $bannedAt;

    /**
     * BlockedIP constructor.
     */
    public function __construct()
    {
        $this->lastAttemptAt = new \DateTime('now');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @param int $attempts
     */
    public function setAttempts(int $attempts): void
    {
        $this->attempts = $attempts;
    }

    /**
     * @return \DateTime
     */
    public function getLastAttemptAt(): \DateTime
    {
        return $this->lastAttemptAt;
    }

    /**
     * @param \DateTime $lastAttemptAt
     */
    public function setLastAttemptAt(\DateTime $lastAttemptAt): void
    {
        $this->lastAttemptAt = $lastAttemptAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getBannedAt(): ?\DateTime
    {
        return $this->bannedAt;
    }

    /**
     * @param \DateTime|null $bannedAt
     */
    public function setBannedAt(?\DateTime $bannedAt): void
    {
        $this->bannedAt = $bannedAt;
    }

    ######################################################
    #                                                    #
    #                   Helper Methods                   #
    #                                                    #
    ######################################################
}
