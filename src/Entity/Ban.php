<?php

namespace NetBull\SecurityBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use NetBull\SecurityBundle\Repository\BanRepository;

#[ORM\Table(name: 'security_bans')]
#[ORM\Entity(repositoryClass: BanRepository::class)]
class Ban extends BaseListing
{
    /**
     * @var int|null
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @var DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $expireAt = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getExpireAt(): ?DateTimeInterface
    {
        return $this->expireAt;
    }

    /**
     * @param DateTimeInterface|null $expireAt
     * @return Ban
     */
    public function setExpireAt(?DateTimeInterface $expireAt): Ban
    {
        $this->expireAt = $expireAt;
        return $this;
    }
}
