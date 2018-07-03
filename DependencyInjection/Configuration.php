<?php

namespace NetBull\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package NetBull\SecurityBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('netbull_security');

        $rootNode
            ->children()
                ->scalarNode('banned_route')->defaultNull()->end()
                ->scalarNode('fingerprint')->defaultValue('netbull_security.fingerprint.browser')->end()
                ->integerNode('attempts_threshold')
                    ->min(0)
                    ->defaultValue(300)
                ->end()
                ->integerNode('max_attempts')
                    ->min(0)
                    ->defaultValue(5)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
