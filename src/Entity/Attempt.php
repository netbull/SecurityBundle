<?php

namespace NetBull\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use NetBull\SecurityBundle\Repository\AttemptRepository;

#[ORM\Table(name: 'security_attempts')]
#[ORM\Entity(repositoryClass: AttemptRepository::class)]
class Attempt extends BaseListing
{
    /**
     * @var int|null
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
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
}
