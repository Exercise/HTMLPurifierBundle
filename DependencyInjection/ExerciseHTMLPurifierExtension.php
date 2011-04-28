<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Processor;

class ExerciseHTMLPurifierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        foreach ($config as $name => $options) {
            $configServiceId = $this->getAlias().'.config.'.$name;
            $configDefinition = new Definition('HTMLPurifier_Config');
            $configDefinition
                ->setFactoryClass('HTMLPurifier_Config')
                ->setFactoryMethod('createDefault')
            ;

            foreach ($options as $key => $value) {
                $configDefinition->addMethodCall('set', array($key, $value));
            }

            $container->setDefinition($configServiceId, $configDefinition);
            
            $purifierDefinition = new Definition('HTMLPurifier', array(new Reference($configServiceId)));
            $container->setDefinition($this->getAlias().'.'.$name, $purifierDefinition);
        }
    }

    public function getAlias()
    {
        return 'exercise_html_purifier';
    }
}
