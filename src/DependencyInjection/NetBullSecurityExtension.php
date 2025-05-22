<?php

namespace NetBull\SecurityBundle\DependencyInjection;

use Exception;
use NetBull\SecurityBundle\EventListener\SecurityListener;
use NetBull\SecurityBundle\Managers\SecurityManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NetBullSecurityExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('fingerprints.yaml');
        $loader->load('listeners.yaml');

        $service = $container->getDefinition(SecurityListener::class);
        $service->replaceArgument(2, $config['banned_route']);
        $service->replaceArgument(3, $config['unbanned_route']);

        $service = $container->getDefinition(SecurityManager::class);
        $service->replaceArgument(0, $config['max_attempts']);
        $service->replaceArgument(1, $config['attempts_threshold']);
        $service->replaceArgument(2, $config['ban_threshold']);

        $fingerprintService = $config['fingerprint'];
        if (in_array($fingerprintService, ['browser', 'ip'])) {
            $fingerprintService = 'netbull_security.fingerprint.' . $fingerprintService;
        }

        $service->replaceArgument(3, $fingerprintService);
        $service->replaceArgument(4, $config['garbage_collect']['probability']);
        $service->replaceArgument(5, $config['garbage_collect']['divider']);
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'netbull_security';
    }
}
