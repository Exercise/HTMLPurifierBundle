<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection\Compiler;

use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class HTMLPurifierPass implements CompilerPassInterface
{
    const PURIFIER_TAG = 'exercise.html_purifier';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasAlias(HTMLPurifiersRegistryInterface::class)) {
            return;
        }

        try {
            $registry = $container->findDefinition(HTMLPurifiersRegistryInterface::class);
        } catch (ServiceNotFoundException $e) {
            return;
        }

        $purifiers = [];

        foreach ($container->findTaggedServiceIds(self::PURIFIER_TAG) as $id => $tags) {
            if (empty($tags[0]['profile'])) {
                throw new InvalidConfigurationException(sprintf('Tag "%s" must define a "profile" attribute.', self::PURIFIER_TAG));
            }

            $profile = $tags[0]['profile'];
            $purifier = $container->getDefinition($id);

            if (empty($purifier->getArguments())) {
                $configId = "exercise_html_purifier.config.$profile";
                $config = $container->hasDefinition($configId) ? $configId : 'exercise_html_purifier.config.default';

                $purifier->addArgument(new Reference($config));
            }

            $purifiers[$profile] = new Reference($id);
        }

        $registry->setArguments([
            ServiceLocatorTagPass::register($container, $purifiers),
        ]);
    }
}
