<?php

namespace NetBull\SecurityBundle\Fingerprints;

use BrowscapPHP\Browscap;
use BrowscapPHP\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\HttpFoundation\Request;

class Browser extends BaseFingerprint
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $projectDir
     * @param LoggerInterface $logger
     */
    public function __construct(string $projectDir, LoggerInterface $logger)
    {
        $this->cacheDir = $projectDir . '/vendor/browscap/browscap-php/resources/';
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function compute(?Request $request = null): ?string
    {
        $adapter = new FilesystemAdapter('browser', 0, $this->cacheDir);
        $cache = new Psr16Cache($adapter);
        $bc = new Browscap($cache, $this->logger);

        try {
            $result = json_encode($bc->getBrowser());
        } catch (Exception $e) {
            return null;
        }

        $this->data = json_decode($result, true);

        return md5($result);
    }
}
