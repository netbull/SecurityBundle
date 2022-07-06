<?php

namespace NetBull\SecurityBundle\Fingerprints;

use Symfony\Component\HttpFoundation\Request;

abstract class BaseFingerprint implements FingerprintInterface
{
    /**
     * @var null|array
     */
    protected $data = null;

    /**
     * @param Request|null $request
     */
    abstract public function compute(?Request $request = null);

    /**
     * @return array|null
     */
    public function getFingerprintData(): ?array
    {
        return $this->data;
    }
}
