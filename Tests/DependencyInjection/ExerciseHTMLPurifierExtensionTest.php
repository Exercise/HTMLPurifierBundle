<?php

namespace Exercise\HTMLPurifierBundle\Tests\DependencyInjection;

use Exercise\HTMLPurifierBundle\DependencyInjection\ExerciseHTMLPurifierExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

    private $defaultConfig;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ExerciseHTMLPurifierExtension();

        $this->defaultConfig = array(
            'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier',
        );
    }

    public function testShouldLoadDefaultConfiguration()
    {
        $this->extension->load(array(), $this->container);

        $this->assertDefaultConfigDefinition($this->defaultConfig);
        $this->assertCacheWarmerSerializerPaths(array('%kernel.cache_dir%/htmlpurifier'));
    }

    public function testShouldAllowOverridingDefaultConfigurationCacheSerializerPath()
    {
        $config = array(
            'default' => array(
                'AutoFormat.AutoParagraph' => true,
                'Cache.SerializerPath'     => null,
            ),
        );

        $this->extension->load(array($config), $this->container);

        $this->assertDefaultConfigDefinition($config['default']);
        $this->assertCacheWarmerSerializerPaths(array());
    }

    public function testShouldNotDeepMergeOptions()
    {
        $configs = array(
            array('default' => array(
                'Core.HiddenElements'  => array('script' => true),
                'Cache.SerializerPath' => null,
            )),
            array('default' => array(
                'Core.HiddenElements'  => array('style' => true),
            )),
        );

        $this->extension->load($configs, $this->container);

        $this->assertDefaultConfigDefinition(array(
            'Core.HiddenElements'  => array('style' => true),
            'Cache.SerializerPath' => null,
        ));
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
                'Cache.SerializerPath'              => '%kernel.cache_dir%/htmlpurifier-simple',
                'AutoFormat.Linkify'                => true,
                'AutoFormat.RemoveEmpty'            => true,
                'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
                'HTML.Allowed'                      => "a[href],strong,em,p,li,ul,ol",
            ),
            'advanced' => array(
                'Cache.DefinitionImpl'              => null,
            ),
        );

        $this->extension->load(array($config), $this->container);

        $this->assertDefaultConfigDefinition(array_replace($this->defaultConfig, $config['default']));
        $this->assertConfigDefinition('simple', $config['simple']);
        $this->assertConfigDefinition('advanced', $config['advanced']);

        $this->assertCacheWarmerSerializerPaths(array(
            '%kernel.cache_dir%/htmlpurifier',
            '%kernel.cache_dir%/htmlpurifier-simple',
        ));
    }

    public function testShouldResolveServices()
    {
        $container = new ContainerBuilder;
        $extension = new ExerciseHTMLPurifierExtension();

        $config = array(
            'simple' => array(
                'AutoFormat.Custom' => array('@service_container'),
            ),
        );

        $this->extension->load(array($config), $this->container);

        $definition = $this->container->getDefinition('exercise_html_purifier.config.simple');
        $calls = $definition->getMethodCalls();

        $call = $calls[0];
        $this->assertSame('loadArray', $call[0]);

        $args = $call[1];

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $args[0]['AutoFormat.Custom'][0]);
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
        $this->assertEquals('%exercise_html_purifier.config.class%', $definition->getClass());
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
        $this->assertEquals('%exercise_html_purifier.config.class%', $definition->getClass());
        $this->assertEquals('%exercise_html_purifier.config.class%', $definition->getFactoryClass());
        $this->assertEquals('create', $definition->getFactoryMethod());
        $this->assertEquals(array($config), $definition->getArguments());
    }

    /**
     * Assert that the cache warmer serializer paths equal the given array.
     *
     * @param array $paths
     */
    private function assertCacheWarmerSerializerPaths(array $paths)
    {
        $this->assertEquals($paths, $this->container->getParameter('exercise_html_purifier.cache_warmer.serializer.paths'));
    }
}
