<?php

namespace NetBull\SecurityBundle\Fingerprints;

use Symfony\Component\HttpFoundation\Request;

abstract class BaseFingerprint implements FingerprintInterface
{
    /**
     * @var array|null
     */
    protected ?array $data = null;

    /**
     * @param Request|null $request
     * @return string|null
     */
    abstract public function compute(?Request $request = null): ?string;

    /**
     * @return array|null
     */
    public function getFingerprintData(): ?array
    {
        return $this->data;
    }
}
