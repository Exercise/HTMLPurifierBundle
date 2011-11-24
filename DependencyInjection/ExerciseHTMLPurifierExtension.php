<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;


class ExerciseHTMLPurifierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('html_purifier.xml');

        $configs = $this->processConfiguration(new Configuration(), $configs);
        $configs = array_replace(array('default' => array()), $configs);

        foreach ($configs as $name => $options) {

            $options = array_replace(
                array('Cache.SerializerPath' => $container->getParameter('kernel.cache_dir') . '/htmlpurifier'),
                $options
            );

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
