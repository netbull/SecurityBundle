<?php

namespace NetBull\SecurityBundle\Fingerprints;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class Ip
 * @package NetBull\SecurityBundle\Fingerprints
 */
class Ip implements FingerprintInterface
{
    /**
     * {@inheritdoc}
     */
    public function compute(?Request $request = null)
    {
        if ($request && $request->getClientIp()) {
            return $request->getClientIp();
        }

        return null;
    }
}
