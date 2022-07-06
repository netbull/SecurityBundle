<?php

namespace NetBull\SecurityBundle\Fingerprints;

use BrowscapPHP\Browscap;
use BrowscapPHP\Exception;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;

class Browser extends BaseFingerprint
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param CacheInterface $cache
     */
    public function __construct(LoggerInterface $logger, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function compute(?Request $request = null): ?string
    {
        $bc = new Browscap($this->cache, $this->logger);

        try {
            $result = json_encode($bc->getBrowser());
        } catch (Exception $e) {
            return null;
        }

        $this->data = json_decode($result, true);

        return md5($result);
    }
}
