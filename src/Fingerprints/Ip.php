<?php

namespace NetBull\SecurityBundle\Fingerprints;

use Symfony\Component\HttpFoundation\Request;

class Ip extends BaseFingerprint
{
    /**
     * @param Request|null $request
     * @return string|null
     */
    public function compute(?Request $request = null): ?string
    {
        if ($request && $request->getClientIp()) {
            return $request->getClientIp();
        }

        return null;
    }
}
