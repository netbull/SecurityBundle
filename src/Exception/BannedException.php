<?php

namespace NetBull\SecurityBundle\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BannedException extends AccessDeniedHttpException
{
    public function __construct()
    {
        parent::__construct('You are banned, you may try later.');
    }
}
