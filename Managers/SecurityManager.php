<?php

namespace NetBull\SecurityBundle\Managers;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;

use NetBull\SecurityBundle\Entity\Listed;
use NetBull\SecurityBundle\Entity\Attempt;
use NetBull\SecurityBundle\Repository\ListedRepository;
use NetBull\SecurityBundle\Repository\AttemptRepository;
use NetBull\SecurityBundle\Fingerprints\FingerprintInterface;

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
     * @var FingerprintInterface
     */
    protected $fingerprint;

    /**
     * @var AttemptRepository
     */
    protected $attemptRepository;

    /**
     * @var ListedRepository
     */
    protected $listedRepository;

    /**
     * @var array
     */
    protected $list = [];

    /**
     * SecurityManager constructor.
     * @param int $maxAttempts
     * @param int $attemptsThreshold
     * @param FingerprintInterface $fingerprint
     * @param AttemptRepository $attemptRepository
     * @param ListedRepository $listedRepository
     */
    public function __construct(int $maxAttempts, int $attemptsThreshold, FingerprintInterface $fingerprint, AttemptRepository $attemptRepository, ListedRepository $listedRepository)
    {
        $this->maxAttempts = $maxAttempts;
        $this->attemptsThreshold = $attemptsThreshold;
        $this->fingerprint = $fingerprint;
        $this->attemptRepository = $attemptRepository;
        $this->listedRepository = $listedRepository;

        $this->refreshLists();
        $this->removeOldRecords();
    }

    /**
     * @param Request $request
     * @return bool
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
            return false;
        }

        $listedRecord = $this->isListed($fingerprint);

        if ($listedRecord) {
            switch ($listedRecord['action']) {
                case Listed::ACTION_ALLOW:
                    return false;
                case Listed::ACTION_DENY:
                    return true;
            }
        } elseif ($this->isMaxAttemptsExceeded($fingerprint)) {
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
        return $this->attemptRepository->countAttempts($fingerprint) >= $this->maxAttempts;
    }

    ######################################################
    #                                                    #
    #                   Helper Methods                   #
    #                                                    #
    ######################################################

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
     * Removes old attempts
     */
    private function removeOldRecords()
    {
        $oldRecordsTime = new \DateTime('now');
        $oldRecordsTime->modify('- ' . $this->attemptsThreshold . ' seconds');

        $this->attemptRepository->removeOldRecords($oldRecordsTime);
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
     * @param Request $request
     * @return null|string
     */
    public function computeFingerprint(Request $request)
    {
        return $this->fingerprint->compute($request);
    }
}
