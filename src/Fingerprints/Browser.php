<?php

namespace NetBull\SecurityBundle\Fingerprints;

use BrowscapPHP\Browscap;
use BrowscapPHP\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Browser
 * @package NetBull\SecurityBundle\Fingerprints
 */
class Browser extends BaseFingerprint
{
    /**
     * Cache dir for the Browscap
     * @var string
     */
    private $cacheDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Browser constructor.
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
        $fileCache = new \Doctrine\Common\Cache\FilesystemCache($this->cacheDir);
        $cache = new \Roave\DoctrineSimpleCache\SimpleCacheAdapter($fileCache);

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
