<?php

namespace NetBull\SecurityBundle\Entity;

use DateTime;
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
     * @var string|null
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string")
     */
    private $fingerprint;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var string|null
     *
     * @Assert\Ip
     * @ORM\Column(type="string", nullable=true)
     */
    private $ip;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $metaData;

    /**
     * Attempt constructor.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime('now');
    }

    /**
     * @return string|null
     */
    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    /**
     * @param string|null $fingerprint
     * @return BaseListing
     */
    public function setFingerprint(?string $fingerprint): BaseListing
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return BaseListing
     */
    public function setCreatedAt(DateTime $createdAt): BaseListing
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     * @return BaseListing
     */
    public function setIp(?string $ip): BaseListing
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    /**
     * @param array|null $metaData
     * @return BaseListing
     */
    public function setMetaData(?array $metaData): BaseListing
    {
        $this->metaData = $metaData;

        return $this;
    }

    ######################################################
    #                   Helper Methods                   #
    ######################################################
    /**
     * @param BaseListing $listing
     */
    public function copy(BaseListing $listing)
    {
        $this->setFingerprint($listing->getFingerprint());
        $this->setIp($listing->getIp());
        $this->setMetaData($listing->getMetaData());
    }
}
