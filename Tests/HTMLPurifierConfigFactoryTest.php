<?php

namespace Exercise\HTMLPurifierBundle\Tests;

use Exercise\HTMLPurifierBundle\HTMLPurifierConfigFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class HTMLPurifierConfigFactoryTest extends TestCase
{
    private static $cacheDir;

    public static function setUpBeforeClass(): void
    {
        self::$cacheDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'html_purifier';
        (new Filesystem())->mkdir(self::$cacheDir);
    }

    public static function tearDownAfterClass(): void
    {
        (new Filesystem())->remove(self::$cacheDir);
    }

    public function testCreateUseDoesNotBuildDefinitionByDefault()
    {
        TestHTMLPurifierConfigFactory::create('default', []);

        $this->assertSame(0, TestHTMLPurifierConfigFactory::$calledBuild);
    }

    public function testCreateUseSerializedCache()
    {
        $configArgs = [
            'test', /* profile */
            [/* config array */
                'Cache.SerializerPath' => self::$cacheDir,
                'HTML.Nofollow' => true,
            ],
            null, /* default config */
            [], /* parents */
            ['a' => ['href' => 'URI']], /* attributes */
        ];

        (new \HTMLPurifier(
            TestHTMLPurifierConfigFactory::create(...$configArgs)
        ))->purify('<div>test</div>');

        TestHTMLPurifierConfigFactory::create(...$configArgs);

        $this->assertSame(1, TestHTMLPurifierConfigFactory::$calledBuild);
    }
}

class TestHTMLPurifierConfigFactory extends HTMLPurifierConfigFactory
{
    public static $calledBuild = 0;

    public static function buildHTMLDefinition(
        \HTMLPurifier_Definition $def,
        array $attributes,
        array $elements,
        array $blankElements
    ): void {
        ++self::$calledBuild;
        parent::buildHTMLDefinition($def, $attributes, $elements, $blankElements);
    }
}
