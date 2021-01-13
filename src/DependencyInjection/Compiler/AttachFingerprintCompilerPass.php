<?php

namespace NetBull\SecurityBundle\DependencyInjection\Compiler;

use NetBull\SecurityBundle\Managers\SecurityManager;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AttachFingerprintCompilerPass
 * @package NetBull\SecurityBundle\DependencyInjection\Compiler
 */
class AttachFingerprintCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $manager = $container->getDefinition(SecurityManager::class);
        foreach ($container->findTaggedServiceIds('netbull_security.fingerprint') as $id => $attributes) {
            $manager->addMethodCall('addFingerprint', [$id, new Reference($id)]);
        }
    }
}
