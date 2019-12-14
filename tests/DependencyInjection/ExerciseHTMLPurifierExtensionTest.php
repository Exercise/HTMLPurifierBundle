<?php

namespace Exercise\HTMLPurifierBundle\Tests\DependencyInjection;

use Exercise\HTMLPurifierBundle\DependencyInjection\Compiler\HTMLPurifierPass;
use Exercise\HTMLPurifierBundle\DependencyInjection\ExerciseHTMLPurifierExtension;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistry;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Exercise\HTMLPurifierBundle\Tests\ForwardCompatTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ExerciseHTMLPurifierExtensionTest extends TestCase
{
    use ForwardCompatTestTrait;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var ExerciseHTMLPurifierExtension
     */
    private $extension;

    /**
     * @var array
     */
    private $defaultConfig;

    private function doSetUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ExerciseHTMLPurifierExtension();
        $this->defaultConfig = [
            'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier',
        ];
    }

    private function doTearDown()
    {
        $this->defaultConfig = null;
        $this->extension = null;
        $this->container = null;
    }

    public function testShouldLoadDefaultConfiguration()
    {
        $this->extension->load([], $this->container);

        $this->assertDefaultConfigDefinition($this->defaultConfig);
        $this->assertCacheWarmerSerializerPaths(['%kernel.cache_dir%/htmlpurifier']);
        $this->assertRegistryHasProfiles(['default']);
    }

    public function testShouldAllowOverridingDefaultConfigurationCacheSerializerPath()
    {
        $config = [
            'default' => [
                'AutoFormat.AutoParagraph' => true,
                'Cache.SerializerPath' => null,
            ],
        ];

        $this->extension->load([$config], $this->container);

        $this->assertDefaultConfigDefinition($config['default']);
        $this->assertCacheWarmerSerializerPaths([]);
        $this->assertRegistryHasProfiles(['default']);
    }

    public function testShouldNotDeepMergeOptions()
    {
        $configs = [
            ['default' => [
                'Core.HiddenElements' => ['script' => true],
                'Cache.SerializerPath' => null,
            ]],
            ['default' => [
                'Core.HiddenElements' => ['style' => true],
            ]],
        ];

        $this->extension->load($configs, $this->container);

        $this->assertDefaultConfigDefinition([
            'Core.HiddenElements' => ['style' => true],
            'Cache.SerializerPath' => null,
        ]);
        $this->assertCacheWarmerSerializerPaths([]);
        $this->assertRegistryHasProfiles(['default']);
    }

    public function testShouldLoadCustomConfiguration()
    {
        $config = [
            'default' => [
                'AutoFormat.AutoParagraph' => true,
            ],
            'simple' => [
                'Cache.DefinitionImpl' => null,
                'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier-simple',
                'AutoFormat.Linkify' => true,
                'AutoFormat.RemoveEmpty' => true,
                'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
                'HTML.Allowed' => 'a[href],strong,em,p,li,ul,ol',
            ],
            'advanced' => [
                'Cache.DefinitionImpl' => null,
            ],
        ];

        $this->extension->load([$config], $this->container);

        $this->assertDefaultConfigDefinition(array_replace($this->defaultConfig, $config['default']));
        $this->assertConfigDefinition('simple', $config['simple']);
        $this->assertConfigDefinition('advanced', $config['advanced']);
        $this->assertCacheWarmerSerializerPaths([
            '%kernel.cache_dir%/htmlpurifier',
            '%kernel.cache_dir%/htmlpurifier-simple',
        ]);
        $this->assertRegistryHasProfiles(['default', 'simple', 'advanced']);
    }

    /**
     * Asserts that the named config definition extends the default profile and
     * loads the given options.
     *
     * @param string $name
     */
    private function assertConfigDefinition($name, array $config)
    {
        $this->assertTrue($this->container->hasDefinition('exercise_html_purifier.config.'.$name));

        $definition = $this->container->getDefinition('exercise_html_purifier.config.'.$name);

        $this->assertSame([\HTMLPurifier_Config::class, 'inherit'], $definition->getFactory());

        $args = $definition->getArguments();

        $this->assertCount(1, $args);
        $this->assertEquals([$config], $definition->getMethodCalls()[0][1]);
    }

    /**
     * Asserts that the default config definition loads the given options.
     */
    private function assertDefaultConfigDefinition(array $config)
    {
        $this->assertTrue($this->container->hasDefinition('exercise_html_purifier.config.default'));

        $definition = $this->container->getDefinition('exercise_html_purifier.config.default');
        $this->assertEquals([\HTMLPurifier_Config::class, 'create'], $definition->getFactory());
        $this->assertEquals([$config], $definition->getArguments());
    }

    /**
     * Asserts that the registry has profiles.
     *
     * @param string[] $profiles
     */
    private function assertRegistryHasProfiles(array $profiles)
    {
        $this->assertTrue($this->container->hasAlias(HTMLPurifiersRegistryInterface::class), 'The registry interface alias must exist.');

        try {
            $registry = $this->container->findDefinition(HTMLPurifiersRegistryInterface::class);
        } catch (ServiceNotFoundException $e) {
            $this->fail(sprintf('Alias %s does not target a valid id: %s.', HTMLPurifiersRegistryInterface::class, $e->getMessage()));
        }

        $this->assertSame(HTMLPurifiersRegistry::class, $registry->getClass());

        foreach ($profiles as $profile) {
            $purifierId = "exercise_html_purifier.$profile";

            $this->assertTrue($this->container->has($purifierId), "The service $purifierId should be registered.");

            $tag = ['profile' => $profile];
            $purifier = $this->container->findDefinition($purifierId);

            $this->assertSame([HTMLPurifierPass::PURIFIER_TAG => [$tag]], $purifier->getTags());
        }
    }

    /**
     * Assert that the cache warmer serializer paths equal the given array.
     */
    private function assertCacheWarmerSerializerPaths(array $paths)
    {
        $this->assertEquals($paths, $this->container->getParameter('exercise_html_purifier.cache_warmer.serializer.paths'));
    }
}
