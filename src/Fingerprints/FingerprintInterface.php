<?php

namespace NetBull\SecurityBundle\Fingerprints;

use Symfony\Component\HttpFoundation\Request;

interface FingerprintInterface
{
    /**
     * @param Request|null $request
     * @return string|null
     */
    public function compute(?Request $request = null): ?string;

    /**
     * @return array|null
     */
    public function getFingerprintData(): ?array;
}
