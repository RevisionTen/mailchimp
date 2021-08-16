<?php

namespace RevisionTen\Mailchimp\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('mailchimp');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('api_key')->end()
                ->arrayNode('campaigns')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('list_id')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
