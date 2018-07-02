<?php

namespace NetBull\SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use NetBull\Security\DependencyInjection\NetBullSecurityExtension;

/**
 * Class NetBullSecurityBundle
 * @package NetBull\SecurityBundle
 */
class NetBullSecurityBundle extends Bundle
{
    /**
     * @return NetBullSecurityExtension|null|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new NetBullSecurityExtension();
    }
}
