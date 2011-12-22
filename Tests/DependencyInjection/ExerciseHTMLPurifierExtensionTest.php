<?php

namespace Exercise\HTMLPurfierBundle\Tests\DependencyInjection;

use Exercise\HTMLPurifierBundle\DependencyInjection\ExerciseHTMLPurifierExtension;
use Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;

class ExerciseHTMLPurifierExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * @var Exercise\HTMLPurifierBundle\DependencyInjection\ExerciseHTMLPurifierExtension
     */
    private $extension;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ExerciseHTMLPurifierExtension();
    }

    public function testShouldLoadDefaultConfiguration()
    {
        $this->extension->load(array(), $this->container);

        $this->assertDefaultConfigDefinition(array(
            'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier',
        ));
    }

    public function testShouldAllowOverridingDefaultConfiguration()
    {
        $this->extension->load(array(array('default' => null)), $this->container);

        $this->assertDefaultConfigDefinition(array());
    }

    public function testShouldLoadCustomConfiguration()
    {
        $container = new ContainerBuilder();
        $extension = new ExerciseHTMLPurifierExtension();

        $config = array(
            'default' => array(
                'AutoFormat.AutoParagraph'          => true,
            ),
            'simple' => array(
                'Cache.DefinitionImpl'              => null,
                'AutoFormat.Linkify'                => true,
                'AutoFormat.RemoveEmpty'            => true,
                'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
                'HTML.Allowed'                      => "a[href],strong,em,p,li,ul,ol"
            ),
            'advanced' => array(
                'Cache.DefinitionImpl'              => null,
            ),
        );

        $this->extension->load(array($config), $this->container);

        $this->assertDefaultConfigDefinition($config['default']);
        $this->assertConfigDefinition('simple', $config['simple']);
        $this->assertConfigDefinition('advanced', $config['advanced']);
    }

    /**
     * Assert that the named config definition extends the default profile and
     * loads the given options.
     *
     * @param string $name
     * @param array  $config
     */
    private function assertConfigDefinition($name, array $config)
    {
        $this->assertTrue($this->container->hasDefinition('exercise_html_purifier.config.' . $name));

        $definition = $this->container->getDefinition('exercise_html_purifier.config.' . $name);
        $this->assertEquals('%exercise_html_purifier.config.class%', $definition->getFactoryClass());
        $this->assertEquals('inherit', $definition->getFactoryMethod());

        $this->assertEquals(1, count($definition->getArguments()));
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $definition->getArgument(0));
        $this->assertEquals('exercise_html_purifier.config.default', (string) $definition->getArgument(0));

        $calls = $definition->getMethodCalls();
        $this->assertEquals(1, count($calls));
        $this->assertEquals('loadArray', $calls[0][0]);
        $this->assertEquals(array($config), $calls[0][1]);
    }

    /**
     * Assert that the default config definition loads the given options.
     *
     * @param array $config
     */
    private function assertDefaultConfigDefinition(array $config)
    {
        $this->assertTrue($this->container->hasDefinition('exercise_html_purifier.config.default'));

        $definition = $this->container->getDefinition('exercise_html_purifier.config.default');
        $this->assertEquals('%exercise_html_purifier.config.class%', $definition->getFactoryClass());
        $this->assertEquals('create', $definition->getFactoryMethod());
        $this->assertEquals(array($config), $definition->getArguments());
    }
}
