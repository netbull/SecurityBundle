<?php

namespace NetBull\SecurityBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use NetBull\SecurityBundle\DependencyInjection\NetBullSecurityExtension;
use NetBull\SecurityBundle\DependencyInjection\Compiler\AttachFingerprintCompilerPass;

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
     * @return NetBullSecurityExtension|null|ExtensionInterface
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new NetBullSecurityExtension();
    }
}
