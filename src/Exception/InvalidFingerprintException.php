<?php

namespace NetBull\SecurityBundle\Exception;

class InvalidFingerprintException extends \Exception
{
    /**
     * @param string $fingerprint
     */
    public function __construct(string $fingerprint)
    {
        parent::__construct(sprintf('The fingerprint service "%s" does not exists', $fingerprint));
    }
}
