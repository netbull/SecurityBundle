<?php

namespace NetBull\SecurityBundle\Fingerprints;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseFingerprint
 * @package NetBull\SecurityBundle\Fingerprints
 */
abstract class BaseFingerprint implements FingerprintInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * {@inheritdoc}
     */
    abstract public function compute(?Request $request = null);

    /**
     * {@inheritdoc}
     */
    public function getFingerprintData()
    {
        return $this->data;
    }
}
