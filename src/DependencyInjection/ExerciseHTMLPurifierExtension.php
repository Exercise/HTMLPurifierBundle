<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Exercise\HTMLPurifierBundle\DependencyInjection\Compiler\HTMLPurifierPass;
use Exercise\HTMLPurifierBundle\HTMLPurifierConfigFactory;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistry;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ExerciseHTMLPurifierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('html_purifier.xml');

        $configs = $this->processConfiguration(new Configuration(), $configs);

        // Set default serializer cache path, while ensuring a default profile is defined
        $configs['html_profiles']['default']['config']['Cache.SerializerPath'] = $configs['default_cache_serializer_path'];

        $serializerPaths = [];
        // Drop when require Symfony > 3.4
        $registerAlias = method_exists($container, 'registerAliasForArgument');

        foreach ($configs['html_profiles'] as $name => $definition) {
            $configId = "exercise_html_purifier.config.$name";
            $default = null;
            $parents = []; // stores inherited configs

            if ('default' !== $name) {
                $default = new Reference('exercise_html_purifier.config.default');
                $parentNames = $definition['parents'];

                unset($parentNames['default']); // default is always inherited
                foreach ($parentNames as $parentName) {
                    self::resolveProfileInheritance($parentName, $configs['html_profiles'], $parents);
                }
            }

            $container->register($configId, \HTMLPurifier_Config::class)
                ->setFactory([HTMLPurifierConfigFactory::class, 'create'])
                ->setArguments([
                    $name,
                    $definition['config'],
                    $default,
                    self::getResolvedConfig('config', $parents),
                    self::getResolvedConfig('attributes', $parents, $definition),
                    self::getResolvedConfig('elements', $parents, $definition),
                    self::getResolvedConfig('blank_elements', $parents, $definition),
                ])
            ;

            $id = "exercise_html_purifier.$name";
            $container->register($id, \HTMLPurifier::class)
                ->setArguments([new Reference($configId)])
                ->addTag(HTMLPurifierPass::PURIFIER_TAG, ['profile' => $name])
            ;

            if (isset($definition['config']['Cache.SerializerPath'])) {
                $serializerPaths[] = $definition['config']['Cache.SerializerPath'];
            }

            if ($registerAlias && $default) {
                $container->registerAliasForArgument($id, \HTMLPurifier::class, "$name.purifier");
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
        $container->getDefinition('exercise_html_purifier.cache_warmer.serializer')
            ->setArgument(0, array_unique($serializerPaths))
            ->setArgument(1, array_keys($configs['html_profiles']))
        ;
    }

    public function getAlias()
    {
        return 'exercise_html_purifier';
    }

    private static function resolveProfileInheritance(string $parent, array $configs, array &$resolved): void
    {
        if (isset($resolved[$parent])) {
            // Another profile already inherited this config, skip
            return;
        }

        foreach ($configs[$parent]['parents'] as $grandParent) {
            self::resolveProfileInheritance($grandParent, $configs, $resolved);
        }

        $resolved[$parent]['config'] = $configs[$parent]['config'];
        $resolved[$parent]['attributes'] = $configs[$parent]['attributes'];
        $resolved[$parent]['elements'] = $configs[$parent]['elements'];
        $resolved[$parent]['blank_elements'] = $configs[$parent]['blank_elements'];
    }

    private static function getResolvedConfig(string $parameter, array $parents, array $definition = null): array
    {
        if (null !== $definition) {
            return array_filter(array_merge(
                array_column($parents, $parameter),
                isset($definition[$parameter]) ? $definition[$parameter] : []
            ));
        }

        return array_filter(array_column($parents, $parameter));
    }
}
