<?php

namespace NetBull\SecurityBundle\Fingerprints;

use Symfony\Component\HttpFoundation\Request;

interface FingerprintInterface
{
    /**
     * @param Request|null $request
     * @return mixed
     */
    public function compute(?Request $request = null);

    /**
     * @return array|null
     */
    public function getFingerprintData(): ?array;
}
