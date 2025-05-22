<?php

namespace NetBull\SecurityBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
abstract class BaseListing
{
    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string')]
    private ?string $fingerprint = null;

    /**
     * @var DateTimeInterface
     */
    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    /**
     * @var string|null
     */
    #[Assert\Ip]
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $ip = null;

    /**
     * @var array|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metaData = null;

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
     * @return $this
     */
    public function setFingerprint(?string $fingerprint): self
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
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt): self
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
     * @return $this
     */
    public function setIp(?string $ip): self
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
     * @return $this
     */
    public function setMetaData(?array $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }

    ######################################################
    #                   Helper Methods                   #
    ######################################################
    /**
     * @param BaseListing $listing
     * @return void
     */
    public function copy(BaseListing $listing): void
    {
        $this->setFingerprint($listing->getFingerprint());
        $this->setIp($listing->getIp());
        $this->setMetaData($listing->getMetaData());
    }
}
