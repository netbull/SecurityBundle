<?php

namespace NetBull\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class BaseListing
 * @package NetBull\SecurityBundle\Entity
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseListing
{
    /**
     * @var string
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string")
     */
    private $fingerprint;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var null|string
     *
     * @Assert\Ip
     * @ORM\Column(type="string", nullable=true)
     */
    private $ip;

    /**
     * @var null|array
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $metaData;

    /**
     * Attempt constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @return string
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * @param string $fingerprint
     */
    public function setFingerprint(string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return null|string
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @param null|string $ip
     */
    public function setIp(?string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return null|array
     */
    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    /**
     * @param null|array $metaData
     */
    public function setMetaData(?array $metaData): void
    {
        $this->metaData = $metaData;
    }

    ######################################################
    #                                                    #
    #                   Helper Methods                   #
    #                                                    #
    ######################################################

    public function copy(BaseListing $listing)
    {
        $this->setFingerprint($listing->getFingerprint());
        $this->setIp($listing->getIp());
        $this->setMetaData($listing->getMetaData());
    }
}
