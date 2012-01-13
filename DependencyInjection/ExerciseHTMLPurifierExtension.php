<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $configs = array_map(array($this, 'resolveServices'), $configs);
        $paths = array();

        foreach ($configs as $name => $config) {
            $configDefinition = new Definition('%exercise_html_purifier.config.class%');
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

    private function resolveServices($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, 'resolveServices'), $value);
        } else if (is_string($value) &&  0 === strpos($value, '@')) {
            if (0 === strpos($value, '@?')) {
                $value = substr($value, 2);
                $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } else {
                $value = substr($value, 1);
                $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            }

            if ('=' === substr($value, -1)) {
                $value = substr($value, 0, -1);
                $strict = false;
            } else {
                $strict = true;
            }

            $value = new Reference($value, $invalidBehavior, $strict);
        }

        return $value;
    }
}
