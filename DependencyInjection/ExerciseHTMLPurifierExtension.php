<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;


class ExerciseHTMLPurifierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('html_purifier.xml');

        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        foreach ($config as $name => $options) {
            $configServiceId = $this->getAlias().'.config.'.$name;
            $configDefinition = new Definition('HTMLPurifier_Config');
            $configDefinition
                ->setFactoryClass('%exercise_html_purifier.config.class%')
                ->setFactoryMethod('createDefault')
            ;

            foreach ($options as $key => $value) {
                $configDefinition->addMethodCall('set', array($key, $value));
            }

            $container->setDefinition($configServiceId, $configDefinition);
            
            $purifierDefinition = new Definition('%exercise_html_purifier.class%', array(new Reference($configServiceId)));
            $container->setDefinition($this->getAlias().'.'.$name, $purifierDefinition);
        }
    }

    public function getAlias()
    {
        return 'exercise_html_purifier';
    }
}
