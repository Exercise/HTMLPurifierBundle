<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('exercise_html_purifier');

        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->useAttributeAsKey('name')
                ->prototype('variable')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
