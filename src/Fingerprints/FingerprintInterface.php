<?php

namespace NetBull\SecurityBundle\Fingerprints;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface FingerprintInterface
 * @package NetBull\SecurityBundle\Fingerprints
 */
interface FingerprintInterface
{
    /**
     * @param null|Request $request
     * @return mixed
     */
    public function compute(?Request $request = null);

    /**
     * @return null|array
     */
    public function getFingerprintData(): ?array;
}
