<?php

namespace NetBull\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Listed
 *
 * @ORM\Table(name="security_listed")
 *
 * @UniqueEntity(fields="fingerprint", message="Sorry, you already use this Fingerprint.")
 * @ORM\Entity(repositoryClass="NetBull\SecurityBundle\Repository\ListedRepository")
 */
class Listed
{
    const ACTION_ALLOW = 'allow';
    const ACTION_DENY = 'deny';

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
     * @ORM\Column(type="string")
     */
    private $fingerprint;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5)
     */
    private $action = self::ACTION_ALLOW;

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

    ######################################################
    #                                                    #
    #                   Helper Methods                   #
    #                                                    #
    ######################################################
}
