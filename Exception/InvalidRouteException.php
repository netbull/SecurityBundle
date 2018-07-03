<?php

namespace NetBull\SecurityBundle\Exception;

/**
 * Class InvalidRouteException
 * @package NetBull\SecurityBundle\Exception
 */
class InvalidRouteException extends \Exception
{
    /**
     * InvalidRouteException constructor.
     * @param string $route
     */
    public function __construct(string $route)
    {
        parent::__construct(sprintf('The route "%s" does not exists', $route));
    }
}
