<?php

namespace NetBull\SecurityBundle\Managers;

use NetBull\SecurityBundle\Repository\BanRepository;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;

use NetBull\SecurityBundle\Entity\Ban;
use NetBull\SecurityBundle\Entity\Listed;
use NetBull\SecurityBundle\Entity\Attempt;
use NetBull\SecurityBundle\Repository\ListedRepository;
use NetBull\SecurityBundle\Repository\AttemptRepository;
use NetBull\SecurityBundle\Fingerprints\FingerprintInterface;
use NetBull\SecurityBundle\Exception\InvalidFingerprintException;

/**
 * Class SecurityManager
 * @package NetBull\SecurityBundle\Managers
 */
class SecurityManager
{
    /**
     * @var int
     */
    protected $maxAttempts;

    /**
     * @var int
     */
    protected $attemptsThreshold;

    /**
     * @var int
     */
    protected $banThreshold;

    /**
     * @var string
     */
    protected $fingerprintName;

    /**
     * @var int
     */
    protected $gcProbability;

    /**
     * @var int
     */
    protected $gcDivisor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AttemptRepository
     */
    protected $attemptRepository;

    /**
     * @var ListedRepository
     */
    protected $listedRepository;

    /**
     * @var BanRepository
     */
    protected $banRepository;

    /**
     * @var array
     */
    protected $list = [];

    /**
     * @var FingerprintInterface[]
     */
    protected $fingerprints = [];

    /**
     * SecurityManager constructor.
     *
     * @param int $maxAttempts
     * @param int $attemptsThreshold
     * @param int $banThreshold
     * @param null|string $fingerprintName
     * @param int $gcProbability
     * @param int $gcDivisor
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(int $maxAttempts, int $attemptsThreshold, int $banThreshold, ?string $fingerprintName, int $gcProbability, int $gcDivisor, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->maxAttempts = $maxAttempts;
        $this->attemptsThreshold = $attemptsThreshold;
        $this->banThreshold = $banThreshold;
        $this->fingerprintName = $fingerprintName;
        $this->gcProbability = $gcProbability;
        $this->gcDivisor = $gcDivisor;
        $this->logger = $logger;
        $this->attemptRepository = $em->getRepository(Attempt::class);
        $this->listedRepository = $em->getRepository(Listed::class);
        $this->banRepository = $em->getRepository(Ban::class);

        $this->refreshLists();
        $this->removeOldRecords();
    }

    /**
     * @param string $name
     * @param FingerprintInterface $fingerprint
     */
    public function addFingerprint(string $name, FingerprintInterface $fingerprint)
    {
        $this->fingerprints[$name] = $fingerprint;
    }

    /**
     * @param null|string $name
     * @return FingerprintInterface
     * @throws InvalidFingerprintException
     */
    public function getFingerprint(?string $name = null)
    {
        $name = $name ?? $this->fingerprintName;

        if (!isset($this->fingerprints[$name])) {
            throw new InvalidFingerprintException($name);
        }

        return $this->fingerprints[$name];
    }

    /**
     * @param Request $request
     * @return bool
     * @throws InvalidFingerprintException
     */
    public function storeAttempt(Request $request)
    {
        $fingerprint = $this->computeFingerprint($request);

        $listed = $this->isListed($fingerprint);

        if ($listed) {
            return false;
        }

        $attempt = $this->getAttempt($fingerprint);
        $this->attemptRepository->save($attempt);

        $this->log(sprintf('Stored fingerprint "%s".', $fingerprint));

        if ($this->isMaxAttemptsExceeded($fingerprint)) {
            $ban = new Ban();
            $ban->setFingerprint($fingerprint);
            $ban->setExpireAt($this->getBanExpirationTime());
            $this->banRepository->save($ban);
        }

        return true;
    }

    ######################################################
    #                                                    #
    #                         Tests                      #
    #                                                    #
    ######################################################

    /**
     * @param $fingerprint
     * @return mixed|null
     */
    public function isListed($fingerprint)
    {
        foreach ($this->list as $listedRecord) {
            if (false !== ip2long($fingerprint) && IpUtils::checkIp($fingerprint, $listedRecord['fingerprint'])) {
                $this->log(sprintf('Fingerprint "%s" is empty.', $fingerprint));
                return $listedRecord;
            } else if ($fingerprint === $listedRecord['fingerprint']) {
                return $listedRecord;
            }
        }

        return null;
    }

    /**
     * @param null|string $fingerprint
     * @return bool
     */
    public function isBlocked(?string $fingerprint)
    {
        if (!$fingerprint) {
            $this->log(sprintf('Fingerprint "%s" is empty.', $fingerprint));
            return false;
        }

        $listedRecord = $this->isListed($fingerprint);

        if ($listedRecord) {
            switch ($listedRecord['action']) {
                case Listed::ACTION_ALLOW:
                    $this->log(sprintf('Fingerprint "%s" is whitelisted.', $fingerprint));
                    return false;
                case Listed::ACTION_DENY:
                    $this->log(sprintf('Fingerprint "%s" is blacklisted.', $fingerprint));
                    return true;
            }
        } elseif ($this->banRepository->isBanned($fingerprint)) {
            $this->log(sprintf('Fingerprint "%s" is blocked.', $fingerprint));
            return true;
        }

        return false;
    }

    /**
     * @param string $fingerprint
     * @return bool
     */
    public function isMaxAttemptsExceeded(string $fingerprint)
    {
        return $this->attemptRepository->countAttempts($fingerprint, $this->getFreshAttemptsTime()) >= $this->maxAttempts;
    }

    /**
     * @return bool
     */
    private function shouldGC()
    {
        $percentChanceToGC = 100 * $this->gcProbability / $this->gcDivisor;
        return rand(1, 100) < $percentChanceToGC;
    }

    ######################################################
    #                                                    #
    #                   Helper Methods                   #
    #                                                    #
    ######################################################

    /**
     * @param Request $request
     * @return mixed
     * @throws InvalidFingerprintException
     */
    public function computeFingerprint(Request $request)
    {
        return $this->getFingerprint()->compute($request);
    }

    /**
     * Refreshes the lists
     */
    private function refreshLists()
    {
        if (0 === count($this->list)) {
            $this->list = $this->listedRepository->getAll();
        }
    }

    /**
     * @return \DateTime
     */
    private function getFreshAttemptsTime()
    {
        $time = new \DateTime('- ' . $this->attemptsThreshold . ' seconds');
        return $time;
    }

    /**
     * @return \DateTime
     */
    private function getBanExpirationTime()
    {
        $time = new \DateTime('+ ' . $this->banThreshold . ' seconds');
        return $time;
    }

    /**
     * Removes old attempts
     */
    private function removeOldRecords()
    {
        if (!$this->shouldGC()) {
            return;
        }

        $this->log(sprintf('Removing old attempts.'));
        $this->attemptRepository->removeOldRecords($this->getFreshAttemptsTime());
    }

    /**
     * @param $fingerprint
     * @return Attempt
     */
    private function getAttempt($fingerprint)
    {
        $attempt = new Attempt();
        $attempt->setFingerprint($fingerprint);

        return $attempt;
    }

    /**
     * @param string $message
     */
    private function log(string $message)
    {
        $this->logger->info("[ NS ] " . $message);
    }
}
