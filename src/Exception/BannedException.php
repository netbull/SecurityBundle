<?php

namespace NetBull\SecurityBundle\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class BannedException
 * @package NetBull\SecurityBundle\Exception
 */
class BannedException extends AccessDeniedHttpException
{
    /**
     * BannedException constructor.
     */
    public function __construct()
    {
        parent::__construct('You are banned, you may try later.');
    }
}
