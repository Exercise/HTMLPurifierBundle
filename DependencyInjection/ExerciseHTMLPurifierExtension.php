<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Exercise\HTMLPurifierBundle\DependencyInjection\Compiler\HTMLPurifierPass;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistry;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ExerciseHTMLPurifierExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
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
        array_unshift($configs, [
            'default' => [
                'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier',
            ],
        ]);

        $configs = $this->processConfiguration(new Configuration(), $configs);

        $serializerPaths = [];

        foreach ($configs as $name => $config) {
            $configId = "exercise_html_purifier.config.$name";
            $configDefinition = $container->register($configId, \HTMLPurifier_Config::class)
                ->setPublic(false)
            ;

            if ('default' === $name) {
                $configDefinition
                    ->setFactory([\HTMLPurifier_Config::class, 'create'])
                    ->addArgument($config)
                ;
            } else {
                $configDefinition
                    ->setFactory([\HTMLPurifier_Config::class, 'inherit'])
                    ->addArgument(new Reference('exercise_html_purifier.config.default'))
                    ->addMethodCall('loadArray', [$config])
                ;
            }

            $container->register("exercise_html_purifier.$name", \HTMLPurifier::class)
                ->addArgument(new Reference($configId))
                ->addTag(HTMLPurifierPass::PURIFIER_TAG, ['profile' => $name])
            ;

            if (isset($config['Cache.SerializerPath'])) {
                $serializerPaths[] = $config['Cache.SerializerPath'];
            }
        }

        $container->register('exercise_html_purifier.purifiers_registry', HTMLPurifiersRegistry::class)
            ->setPublic(false)
        ;
        $container->setAlias(HTMLPurifiersRegistryInterface::class, 'exercise_html_purifier.purifiers_registry')
            ->setPublic(false)
        ;
        $container->setAlias(\HTMLPurifier::class, 'exercise_html_purifier.default')
            ->setPublic(false)
        ;
        $container->setParameter('exercise_html_purifier.cache_warmer.serializer.paths', array_unique($serializerPaths));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'exercise_html_purifier';
    }
}
