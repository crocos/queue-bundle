<?php

namespace Crocos\QueueBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('crocos_queue');

        $this->addHandlersSection($rootNode);

        return $treeBuilder;
    }

    protected function addHandlersSection(ArrayNodeDefinition $node)
    {
        $node
            ->fixXmlConfig('queue')
            ->children()
                ->arrayNode('queues')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                        ->fixXmlConfig('handler')
                        ->children()
                            ->arrayNode('handlers')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                ->end()
            ->end()
        ;
    }
}
