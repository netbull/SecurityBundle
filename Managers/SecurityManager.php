<?php

namespace NetBull\AuthBundle\Security;

use Symfony\Component\HttpFoundation\Request;

use NetBull\SecurityBundle\Entity\BlockedIP;
use NetBull\SecurityBundle\Repository\ListedIPRepository;
use NetBull\SecurityBundle\Repository\BlockedIPRepository;

/**
 * Class SecurityManager
 * @package NetBull\AuthBundle\Security
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
     * @var BlockedIPRepository
     */
    protected $blockedIPRepository;

    /**
     * @var ListedIPRepository
     */
    protected $listedIPRepository;

    /**
     * @var array
     */
    protected $list = [];

    /**
     * @var BlockedIP|null
     */
    protected $attempt;

    /**
     * SecurityManager constructor.
     * @param int $maxAttempts
     * @param int $attemptsThreshold
     */
    public function __construct(int $maxAttempts, int $attemptsThreshold)
    {
        $this->maxAttempts = $maxAttempts;
        $this->attemptsThreshold = $attemptsThreshold;
    }

    /**
     * @param BlockedIPRepository $blockedIPRepository
     */
    public function setBlockedIPRepository(BlockedIPRepository $blockedIPRepository)
    {
        $this->blockedIPRepository = $blockedIPRepository;
    }

    /**
     * @param ListedIPRepository $listedIPRepository
     */
    public function setListedIPRepository(ListedIPRepository $listedIPRepository)
    {
        $this->listedIPRepository = $listedIPRepository;
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function storeAttempt(Request $request)
    {
        $this->refreshLists();
        $ip = $request->getClientIp();
        $listedIp = $this->isIPListed($ip);

        if ($listedIp) {
            return false;
        }

        $attempt = $this->getAttempt($ip);
        $attempt->setAttempts($attempt->getAttempts() + 1);

        $this->blockedIPRepository->save($attempt);

        return true;
    }

    /**
     * @param $ip
     * @return mixed|null
     */
    public function isIPListed($ip)
    {
        foreach ($this->list as $listedRecord) {
            if ($this->ipInRange($ip, $listedRecord['ip'])) {
                return $listedRecord;
            }
        }

        return null;
    }

    /**
     * @param string $ip
     * @return null
     */
    public function isIPBlocked(string $ip)
    {
        $oldRecordsTime = new \DateTime('now');
        $oldRecordsTime->modify('- ' . $this->attemptsThreshold . ' seconds');

        $this->blockedIPRepository->removeOldRecords($oldRecordsTime);
        $attempt = $this->getAttempt($ip);

        if ($attempt->getAttempts() > $this->maxAttempts) {
            return true;
        }

        return false;
    }

    /**
     * Refreshes the lists
     */
    private function refreshLists()
    {
        if (0 === count($this->list)) {
            $this->list = $this->listedIPRepository->getAll();
        }
    }

    /**
     * @param $ip
     * @return BlockedIP|null|object
     */
    private function getAttempt($ip)
    {
        $attempt = $this->blockedIPRepository->findOneBy([ 'ip' => $ip ]);

        if (null === $attempt) {
            $attempt = new BlockedIP();
            $attempt->setIp($ip);
        }

        return $attempt;
    }

    /**
     * Check if a given ip is in a network
     *
     * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
     * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
     * @return boolean true if the ip is in this range / false if not.
     */
    private function ipInRange($ip, $range)
    {
        if (false === strpos($range, '/' )) {
            $range .= '/32';
        }

        // $range is in IP/CIDR format eg 127.0.0.1/24
        list($range, $netMask) = explode('/', $range, 2);

        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - $netMask)) - 1;
        $netMaskDecimal = ~ $wildcardDecimal;

        return (($ipDecimal & $netMaskDecimal) === ($rangeDecimal & $netMaskDecimal));
    }
}
