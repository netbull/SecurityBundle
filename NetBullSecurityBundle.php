<?php

namespace NetBull\SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use NetBull\SecurityBundle\DependencyInjection\NetBullSecurityExtension;
use NetBull\SecurityBundle\DependencyInjection\Compiler\AttachFingerprintCompilerPass;

/**
 * Class NetBullSecurityBundle
 * @package NetBull\SecurityBundle
 */
class NetBullSecurityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AttachFingerprintCompilerPass());
    }

    /**
     * @return NetBullSecurityExtension|null|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new NetBullSecurityExtension();
    }
}
