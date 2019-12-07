<?php

namespace Exercise\HTMLPurifierBundle\Tests;

use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class HTMLPurifiersRegistryTest extends TestCase
{
    private $locator;
    private $registry;

    protected function setUp(): void
    {
        $this->locator = $this->createMock(ContainerInterface::class);
        $this->registry = new HTMLPurifiersRegistry($this->locator);
    }

    protected function tearDown(): void
    {
        $this->registry = null;
        $this->locator = null;
    }

    public function provideProfiles(): iterable
    {
        yield ['default'];
        yield ['test'];
    }

    /**
     * @dataProvider provideProfiles
     */
    public function testHas($profile)
    {
        $this->locator->expects($this->once())
            ->method('has')
            ->with($profile)
            ->willReturn(true)
        ;

        $this->assertTrue($this->registry->has($profile));
    }

    /**
     * @dataProvider provideProfiles
     */
    public function testHasNot($profile)
    {
        $this->locator->expects($this->once())
            ->method('has')
            ->with($profile)
            ->willReturn(false)
        ;

        $this->assertFalse($this->registry->has($profile));
    }

    /**
     * @dataProvider provideProfiles
     */
    public function testGet($profile)
    {
        $purifier = $this->createMock(\HTMLPurifier::class);

        $this->locator->expects($this->once())
            ->method('get')
            ->with($profile)
            ->willReturn($purifier)
        ;

        $this->assertSame($purifier, $this->registry->get($profile));
    }
}
