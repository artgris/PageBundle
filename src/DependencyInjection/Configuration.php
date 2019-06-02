<?php

namespace Artgris\Bundle\PageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('artgris_page');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('controllers')
                    ->scalarPrototype()
                    ->end()
                ->end()
                ->arrayNode('types')
                    ->normalizeKeys(false)
                    ->defaultValue([])
                    ->prototype('variable')
                    ->end()
                ->end()
                ->booleanNode('default_types')
                    ->defaultTrue()
                ->end()
                ->booleanNode('hide_route_form')
                    ->defaultFalse()
                ->end()
                ->booleanNode('redirect_after_update')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
