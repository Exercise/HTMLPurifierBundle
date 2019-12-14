<?php

namespace Exercise\HTMLPurifierBundle\Tests\DependencyInjection;

use Exercise\HTMLPurifierBundle\DependencyInjection\Compiler\HTMLPurifierPass;
use Exercise\HTMLPurifierBundle\DependencyInjection\ExerciseHTMLPurifierExtension;
use Exercise\HTMLPurifierBundle\HTMLPurifierConfigFactory;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ExerciseHTMLPurifierExtensionTest extends TestCase
{
    private const DEFAULT_CACHE_PATH = '%kernel.cache_dir%/htmlpurifier';

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

    public function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.cache_dir', '/tmp');
        $this->extension = new ExerciseHTMLPurifierExtension();
        $this->defaultConfig = [
            'Cache.SerializerPath' => self::DEFAULT_CACHE_PATH,
        ];
    }

    public function tearDown(): void
    {
        $this->defaultConfig = null;
        $this->extension = null;
        $this->container = null;
    }

    public function testShouldLoadDefaultConfiguration()
    {
        $this->extension->load([], $this->container);

        $this->assertDefaultConfigDefinition($this->defaultConfig);
        $this->assertCacheWarmerSerializerArgs([self::DEFAULT_CACHE_PATH], ['default']);
        $this->assertRegistryHasProfiles(['default']);
    }

    public function testInvalidParent()
    {
        $config = [
            'html_profiles' => [
                'custom' => [
                    'config' => ['AutoFormat.AutoParagraph' => true],
                ],
                'custom_2' => [
                    'config' => ['AutoFormat.Linkify' => true],
                    'parents' => ['custom', 'unknown'],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid parent "unknown" is not defined for profile "custom_2".');

        $this->extension->load([$config], $this->container);
    }

    /**
     * @dataProvider provideInvalidElementDefinitions
     */
    public function testInvalidElements(array $elementDefinition)
    {
        $config = [
            'html_profiles' => [
                'default' => [
                    'elements' => ['a' => []],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "exercise_html_purifier.html_profiles.default.elements.a": An element definition must define three to five elements: a type ("Inline", "Block", ...), a content type ("Empty", "Optional: #PCDATA", ...), an attributes set ("Core", "Common", ...), and a fourth optional may define attributes rules as array, and fifth for forbidden attributes.');

        $this->extension->load([$config], $this->container);
    }

    public function provideInvalidElementDefinitions(): iterable
    {
        yield 'empty array' => [[]];
        yield 'only one argument' => [['']];
        yield 'only two arguments' => [['', '']];
        yield 'too many arguments' => [['', '', '', [], [], 'extra argument']];
    }

    public function testShouldAllowOverridingDefaultConfigurationCacheSerializerPath()
    {
        $config = [
            'default_cache_serializer_path' => null,
            'html_profiles' => [
                'default' => [
                    'config' => [
                        'AutoFormat.AutoParagraph' => true,
                    ],
                ],
            ],
        ];

        $this->extension->load([$config], $this->container);

        $this->assertDefaultConfigDefinition(array_merge($config['html_profiles']['default']['config'], [
            'Cache.SerializerPath' => null,
        ]));
        $this->assertCacheWarmerSerializerArgs([], ['default']);
        $this->assertRegistryHasProfiles(['default']);
    }

    public function testShouldNotDeepMergeOptions()
    {
        $configs = [
            ['html_profiles' => [
                'default' => [
                    'config' => [
                        'Core.HiddenElements' => ['script' => true],
                    ],
                ],
            ]],
            ['html_profiles' => [
                'default' => [
                    'config' => [
                        'Core.HiddenElements' => ['style' => true],
                    ],
                ],
            ]],
        ];

        $this->extension->load($configs, $this->container);

        $this->assertDefaultConfigDefinition(array_merge([
            'Core.HiddenElements' => ['style' => true],
        ], $this->defaultConfig));
        $this->assertCacheWarmerSerializerArgs([self::DEFAULT_CACHE_PATH], ['default']);
        $this->assertRegistryHasProfiles(['default']);
    }

    public function testShouldLoadCustomConfiguration()
    {
        $config = [
            'html_profiles' => [
                'default' => [
                    'config' => [
                        'AutoFormat.AutoParagraph' => true,
                    ],
                ],
                'simple' => [
                    'config' => [
                        'Cache.DefinitionImpl' => null,
                        'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier-simple',
                        'AutoFormat.Linkify' => true,
                        'AutoFormat.RemoveEmpty' => true,
                        'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
                        'HTML.Allowed' => 'a[href],strong,em,p,li,ul,ol',
                    ],
                ],
                'advanced' => [
                    'config' => [
                        'Cache.DefinitionImpl' => null,
                    ],
                ],
            ],
        ];

        $this->extension->load([$config], $this->container);

        $profiles = ['default', 'simple', 'advanced'];

        $this->assertDefaultConfigDefinition(array_merge($config['html_profiles']['default']['config'], $this->defaultConfig));
        $this->assertConfigDefinition('simple', $config['html_profiles']['simple']['config']);
        $this->assertConfigDefinition('advanced', $config['html_profiles']['advanced']['config']);
        $this->assertCacheWarmerSerializerArgs([
            self::DEFAULT_CACHE_PATH,
            self::DEFAULT_CACHE_PATH.'-simple',
        ], $profiles);
        $this->assertRegistryHasProfiles($profiles);
    }

    public function testShouldLoadComplexCustomConfiguration()
    {
        $defaultConfig = [
            'AutoFormat.AutoParagraph' => true,
        ];
        $defaultAttributes = [
            'a' => ['href' => 'URI'],
            'span' => ['data-link' => 'URI'],
        ];
        $defaultBlankElements = [
            'figcaption',
            'legend',
        ];
        $simpleConfig = [
            'AutoFormat.Linkify' => true,
            'AutoFormat.RemoveEmpty' => true,
            'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
            'HTML.Allowed' => 'a[href],strong,em,p,li,ul,ol',
        ];
        $videoElements = [
            'video' => [
                'Block',
                'Optional: (source, Flow) | (Flow, source) | Flow',
                'Common',
                [
                    'src' => 'URI',
                    'type' => 'Text',
                    'width' => 'Length',
                    'height' => 'Length',
                    'poster' => 'URI',
                    'preload' => 'Enum#auto,metadata,none',
                    'controls' => 'Bool',
                ],
            ],
        ];
        $advancedConfig = [
            'Core.HiddenElements' => ['script' => true],
        ];
        $allParents = ['simple', 'video', 'advanced'];

        $config = [
            'html_profiles' => [
                'default' => [
                    'config' => $defaultConfig,
                    'attributes' => $defaultAttributes,
                    'blank_elements' => $defaultBlankElements,
                ],
                'simple' => [
                    'config' => $simpleConfig,
                ],
                'video' => [
                    'elements' => $videoElements,
                ],
                'advanced' => [
                    'config' => $advancedConfig,
                ],
                'all' => [
                    'parents' => $allParents,
                ],
            ],
        ];

        $this->extension->load([$config], $this->container);

        $profiles = ['default', 'simple', 'video', 'advanced', 'all'];

        $this->assertDefaultConfigDefinition(
            array_merge($defaultConfig, $this->defaultConfig),
            $defaultAttributes,
            [],
            $defaultBlankElements
        );
        $this->assertConfigDefinition('simple', $simpleConfig);
        $this->assertConfigDefinition(
            'video',
            [/* config */],
            [/* parents */],
            [/* attributes */],
            $videoElements
        );
        $this->assertConfigDefinition('advanced', $advancedConfig);
        $this->assertConfigDefinition(
            'all',
            [/* config */],
            [$simpleConfig, /* video config is filtered */ 2 => $advancedConfig],
            [/* attributes */],
            [/* simple elements are filtered */ 1 => $videoElements],
            [/* blank elements */]
        );
        $this->assertCacheWarmerSerializerArgs([self::DEFAULT_CACHE_PATH], $profiles);
        $this->assertRegistryHasProfiles($profiles);
    }

    public function testShouldRegisterAliases()
    {
        if (!method_exists($this->container, 'registerAliasForArgument')) {
            $this->markTestSkipped('Alias arguments binding is not available.');
        }

        $config = [
            'html_profiles' => [
                'default' => [
                    'config' => [
                        'AutoFormat.AutoParagraph' => true,
                    ],
                ],
                'simple' => [
                    'config' => [
                        'HTML.Allowed' => 'a[href],strong,em,p,li,ul,ol',
                    ],
                ],
                'advanced' => [
                    'config' => [
                        'Core.HiddenElements' => ['script' => true],
                    ],
                ],
            ],
        ];

        $this->extension->load([$config], $this->container);

        $this->container->register(ServiceWithDefaultConfig::class)
            ->setAutowired(true)
            ->setPublic(true)
        ;
        $this->container->register(ServiceWithDefaultConfig2::class)
            ->setAutowired(true)
            ->setPublic(true)
        ;
        $this->container->register(ServiceWithSimpleConfig::class)
            ->setAutowired(true)
            ->setPublic(true)
        ;
        $this->container->register(ServiceWithAdvancedConfig::class)
            ->setAutowired(true)
            ->setPublic(true)
        ;

        $this->container->compile();

        $defaultConfigArgument1 = $this->container->findDefinition(ServiceWithDefaultConfig::class)
            ->getArgument(0)
        ;

        $this->assertInstanceOf(Reference::class, $defaultConfigArgument1);
        $this->assertSame('exercise_html_purifier.default', (string) $defaultConfigArgument1);

        $defaultConfigArgument2 = $this->container->findDefinition(ServiceWithDefaultConfig2::class)
            ->getArgument(0)
        ;

        $this->assertInstanceOf(Reference::class, $defaultConfigArgument2);
        $this->assertSame('exercise_html_purifier.default', (string) $defaultConfigArgument2);

        $simpleConfigArgument = $this->container->findDefinition(ServiceWithSimpleConfig::class)
            ->getArgument(0)
        ;

        $this->assertInstanceOf(Definition::class, $simpleConfigArgument);
        $this->assertSame(
            'simple',
            $simpleConfigArgument->getTag(HTMLPurifierPass::PURIFIER_TAG)[0]['profile'] ?? ''
        );

        $advancedConfigArgument = $this->container->findDefinition(ServiceWithAdvancedConfig::class)
            ->getArgument(0)
        ;

        $this->assertInstanceOf(Definition::class, $advancedConfigArgument);
        $this->assertSame(
            'advanced',
            $advancedConfigArgument->getTag(HTMLPurifierPass::PURIFIER_TAG)[0]['profile'] ?? ''
        );
    }

    /**
     * Asserts that the named config definition extends the default profile and
     * loads the given options.
     *
     * @param string $name
     */
    private function assertConfigDefinition($name, array $config, array $parents = [], array $attributes = [], array $elements = [], array $blankElements = [])
    {
        $this->assertTrue($this->container->hasDefinition('exercise_html_purifier.config.'.$name));

        $definition = $this->container->getDefinition('exercise_html_purifier.config.'.$name);

        $this->assertEquals([new Reference(HTMLPurifierConfigFactory::class), 'create'], $definition->getFactory());

        $args = $definition->getArguments();
        $defaultConfig = $definition->getArgument(2);

        $this->assertCount(7, $args);
        $this->assertSame($name, $definition->getArgument(0));
        $this->assertSame($config, $definition->getArgument(1));
        $this->assertInstanceOf(Reference::class, $defaultConfig);
        $this->assertSame('exercise_html_purifier.config.default', (string) $defaultConfig);
        $this->assertSame($parents, $definition->getArgument(3));
        $this->assertSame($attributes, $definition->getArgument(4));
        $this->assertSame($elements, $definition->getArgument(5));
        $this->assertSame($blankElements, $definition->getArgument(6));
    }

    /**
     * Asserts that the default config definition loads the given options.
     */
    private function assertDefaultConfigDefinition(array $config, array $attributes = [], array $elements = [], array $blankElements = []): void
    {
        $this->assertTrue($this->container->hasDefinition('exercise_html_purifier.config.default'));

        $definition = $this->container->getDefinition('exercise_html_purifier.config.default');

        $this->assertEquals([new Reference(HTMLPurifierConfigFactory::class), 'create'], $definition->getFactory());
        $this->assertSame(['default', $config, null, [], $attributes, $elements, $blankElements], $definition->getArguments(), 'Default config is invalid.');
    }

    /**
     * Asserts that the registry has profiles.
     *
     * @param string[] $profiles
     */
    private function assertRegistryHasProfiles(array $profiles): void
    {
        foreach ($profiles as $profile) {
            $this->assertTrue($this->container->hasDefinition("exercise_html_purifier.$profile"));
            $this->assertTrue($this->container->hasDefinition("exercise_html_purifier.config.$profile"));
        }
    }

    /**
     * Assert that the cache warmer serializer paths equal the given array.
     */
    private function assertCacheWarmerSerializerArgs(array $paths, array $profiles): void
    {
        $serializer = $this->container->getDefinition('exercise_html_purifier.cache_warmer.serializer');

        $this->assertSame($serializer->getArgument(0), $paths);
        $this->assertSame($serializer->getArgument(1), $profiles);
        $this->assertSame((string) $serializer->getArgument(2), HTMLPurifiersRegistryInterface::class);
        $this->assertSame((string) $serializer->getArgument(3), 'filesystem');
    }
}

class ServiceWithDefaultConfig
{
    public function __construct(\HTMLPurifier $purifier)
    {
    }
}

class ServiceWithDefaultConfig2
{
    public function __construct(\HTMLPurifier $htmlPurifier)
    {
    }
}

class ServiceWithSimpleConfig
{
    public function __construct(\HTMLPurifier $simplePurifier)
    {
    }
}

class ServiceWithAdvancedConfig
{
    public function __construct(\HTMLPurifier $advancedPurifier)
    {
    }
}
