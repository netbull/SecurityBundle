<?php

namespace NetBull\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use NetBull\SecurityBundle\Exception\InvalidFingerprintException;

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
        $loader->load('fingerprints.yaml');
        $loader->load('security.yaml');

        $service = $container->getDefinition('netbull_security.security_listener');
        $service->replaceArgument(2, $config['banned_route']);

        $service = $container->getDefinition('netbull_security.manager');
        $service->replaceArgument(0, $config['max_attempts']);
        $service->replaceArgument(1, $config['attempts_threshold']);

        $fingerprintService = $config['fingerprint'];
        if (in_array($fingerprintService, ['browser', 'ip'])) {
            $fingerprintService = 'netbull_security.fingerprint.' . $fingerprintService;
        }

        if (!$container->hasDefinition($fingerprintService)) {
            throw new InvalidFingerprintException($fingerprintService);
        }

        $fingerprintDefinition = $container->getDefinition($fingerprintService);
        $service->replaceArgument(2, $fingerprintDefinition);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'netbull_security';
    }
}
