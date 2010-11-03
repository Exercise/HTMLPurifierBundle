<?php

namespace Bundle\ExerciseCom\HTMLPurifierBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HTMLPurifierExtension extends Extension
{
    protected $resources = array(
        'htmlpurifier' => 'htmlpurifier.xml',
    );

    public function apiLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('htmlpurifier')) {
            $this->loadDefaults($container);
        }

        if (isset($config['alias'])) {
            $container->setAlias($config['alias'], 'htmlpurifier');
        }

        foreach (array('allowed_html', 'base_uri', 'absolute_uri', 'namespace') as $attribute) {
            if (isset($config[$attribute])) {
                $container->setParameter('htmlpurifier.'.$attribute, $config[$attribute]);
            }
        }
    }

    public function getAlias()
    {
        return 'htmlpurifier';
    }

    protected function loadDefaults($container)
    {
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
        $loader->load($this->resources['htmlpurifier']);
    }
}
