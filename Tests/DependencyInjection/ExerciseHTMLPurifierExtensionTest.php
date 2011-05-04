<?php

namespace Exercise\HTMLPurfierBundle\Tests\DependencyInjection;

use Exercise\HTMLPurifierBundle\DependencyInjection\ExerciseHTMLPurifierExtension;
use Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;

class ExerciseHTMLPurifierExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ExerciseHTMLPurifierExtension();
        $this->bundle = new ExerciseHTMLPurifierBundle();
        $this->bundle->boot();
    }

    public function testShouldLoadParameters()
    {
        $this->extension->load(array(), $this->container);

        $this->assertEquals('HTMLPurifier', $this->container->getParameter('exercise_html_purifier.class'));
        $this->assertEquals('HTMLPurifier_Config', $this->container->getParameter('exercise_html_purifier.config.class'));
    }

    public function testShouldCreateServicesByConfig()
    {
        $config = array(
            'simple' => array(
                'Cache.DefinitionImpl'              => null,
                'AutoFormat.AutoParagraph'          => true,
                'AutoFormat.Linkify'                => true,
                'AutoFormat.RemoveEmpty'            => true,
                'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
                'HTML.Allowed'                      => "a[href],strong,em,p,li,ul,ol"
            ),
            'advanced' => array(
                'Cache.DefinitionImpl'              => null,
                'AutoFormat.AutoParagraph'          => true,
            ),
        );

        $this->extension->load(array($config), $this->container);

        $this->compileContainer($this->container);

        foreach (array('simple', 'advanced') as $id) {
            $this->assertInstanceOf('HTMLPurifier', $this->container->get('exercise_html_purifier.'.$id));
            $this->assertInstanceOf('HTMLPurifier_Config', $this->container->get('exercise_html_purifier.config.'.$id));
        }
    }

    private function compileContainer(ContainerBuilder $container)
    {
        $container->setParameter('kernel.root_dir', __DIR__);
        $container->getCompilerPassConfig()->setOptimizationPasses(array(
            new ResolveDefinitionTemplatesPass(),
        ));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();
    }    
}
