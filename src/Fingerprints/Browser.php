<?php

namespace NetBull\SecurityBundle\Fingerprints;

use BrowscapPHP\Browscap;
use BrowscapPHP\Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use MatthiasMullie\Scrapbook\Adapters\Flysystem;
use MatthiasMullie\Scrapbook\Psr16\SimpleCache;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class Browser extends BaseFingerprint
{
    /**
     * @var string
     */
    private string $cacheDir;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

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
     * @param Request|null $request
     * @return string|null
     */
    public function compute(?Request $request = null): ?string
    {
        $adapter = new LocalFilesystemAdapter($this->cacheDir);
        $filesystem = new Filesystem($adapter);
        $cache = new SimpleCache(new Flysystem($filesystem));
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
