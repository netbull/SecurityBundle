<?php

namespace NetBull\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use NetBull\SecurityBundle\Repository\ListedRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: 'security_listed')]
#[ORM\Entity(repositoryClass: ListedRepository::class)]
#[UniqueEntity(fields: 'fingerprint', message: 'Sorry, you already use this Fingerprint.')]
class Listed
{
    const string ACTION_ALLOW = 'allow';
    const string ACTION_DENY = 'deny';

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @var string
     */
    #[Assert\Ip]
    #[ORM\Column(type: 'string')]
    private string $fingerprint = '';

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 5)]
    private string $action = self::ACTION_ALLOW;

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
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }
}
