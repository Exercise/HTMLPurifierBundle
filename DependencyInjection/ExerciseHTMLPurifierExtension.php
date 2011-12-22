<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ExerciseHTMLPurifierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('html_purifier.xml');

        /* Prepend the default configuration. This cannot be defined within the
         * Configuration class, since the root node's children are array
         * prototypes.
         *
         * This cache path may be suppressed by either unsetting the "default"
         * configuration (relying on canBeUnset() on the prototype node) or
         * setting the "Cache.SerializerPath" option to null.
         */
        array_unshift($configs, array(
            'default' => array(
                'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier',
            ),
        ));

        $configs = $this->processConfiguration(new Configuration(), $configs);
        $paths = array();

        foreach ($configs as $name => $config) {
            $configDefinition = new Definition();
            $configDefinition->setFactoryClass('%exercise_html_purifier.config.class%');

            if ('default' === $name) {
                $configDefinition
                    ->setFactoryMethod('create')
                    ->addArgument($config);
            } else {
                $configDefinition
                    ->setFactoryMethod('inherit')
                    ->addArgument(new Reference('exercise_html_purifier.config.default'))
                    ->addMethodCall('loadArray', array($config));
            }

            $configId = 'exercise_html_purifier.config.' . $name;
            $container->setDefinition($configId, $configDefinition);

            $container->setDefinition(
                'exercise_html_purifier.' . $name,
                new Definition('%exercise_html_purifier.class%', array(new Reference($configId)))
            );

            if (isset($config['Cache.SerializerPath'])) {
                $paths[] = $config['Cache.SerializerPath'];
            }
        }

        $container->setParameter('exercise_html_purifier.cache_warmer.serializer.paths', array_unique($paths));
    }

    public function getAlias()
    {
        return 'exercise_html_purifier';
    }
}
