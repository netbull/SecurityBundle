<?php

namespace NetBull\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class NetBullSecurityExtension
 * @package NetBull\Security\DependencyInjection
 */
class NetBullSecurityExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('security.yaml');

        $service = $container->getDefinition('netbull_security.security_listener');
        $service->replaceArgument(2, $config['banned_route']);

        $service = $container->getDefinition('netbull_security.manager');
        $service->replaceArgument(0, $config['max_attempts']);
        $service->replaceArgument(1, $config['attempts_threshold']);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'netbull_security';
    }
}
