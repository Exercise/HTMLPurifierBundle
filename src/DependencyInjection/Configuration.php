<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('exercise_html_purifier');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('exercise_html_purifier');
        }

        $rootNode
            ->children()
                ->scalarNode('default_cache_serializer_path')
                    ->defaultValue('%kernel.cache_dir%/htmlpurifier')
                ->end()
                ->arrayNode('html_profiles')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->validate()
                        ->always(function ($profiles) {
                            foreach ($profiles as $profile => $definition) {
                                foreach ($definition['parents'] as $parent) {
                                    if (!isset($profiles[$parent])) {
                                        throw new InvalidConfigurationException(sprintf('Invalid parent "%s" is not defined for profile "%s".', $parent, $profile));
                                    }
                                }
                            }

                            return $profiles;
                        })
                    ->end()
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('config')
                                ->defaultValue([])
                                ->info('An array of parameters.')
                                ->useAttributeAsKey('parameter')
                                ->normalizeKeys(false)
                                ->variablePrototype()->end()
                            ->end()
                            ->arrayNode('attributes')
                                ->defaultValue([])
                                ->info('Every key is a tag name, with arrays for rules')
                                ->normalizeKeys(false)
                                ->useAttributeAsKey('tag_name')
                                ->arrayPrototype()
                                    ->info('Every key is an attribute name for a rule like "Text"')
                                    ->useAttributeAsKey('attribute_name')
                                    ->normalizeKeys(false)
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                            ->arrayNode('elements')
                                ->defaultValue([])
                                ->info('Every key is a tag name, with an array of four values as definition. The fourth is an optional array of attributes rules.')
                                ->normalizeKeys(false)
                                ->useAttributeAsKey('tag_name')
                                ->info('An array represents a definition, with three required elements: a type ("Inline", "Block", ...), a content type ("Empty", "Optional: #PCDATA", ...), an attributes set ("Core", "Common", ...), a fourth optional may define attributes rules as array, and fifth for forbidden attributes.')
                                ->arrayPrototype()
                                    ->validate()
                                        ->ifTrue(function ($array) {
                                            $count = count($array);

                                            return 3 > $count || $count > 5;
                                        })
                                        ->thenInvalid('An element definition must define three to five elements: a type ("Inline", "Block", ...), a content type ("Empty", "Optional: #PCDATA", ...), an attributes set ("Core", "Common", ...), and a fourth optional may define attributes rules as array, and fifth for forbidden attributes.')
                                    ->end()
                                    ->variablePrototype()->end()
                                ->end()
                            ->end()
                            ->arrayNode('blank_elements')
                                ->defaultValue([])
                                ->info('An array of tag names that should purify everything.')
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('parents')
                                ->defaultValue([])
                                ->info('An array of config names that should be inherited.')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
