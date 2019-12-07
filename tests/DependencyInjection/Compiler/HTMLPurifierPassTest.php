<?php

namespace Exercise\HTMLPurifierBundle\Tests\DependencyInjection\Compiler;

use Exercise\HTMLPurifierBundle\DependencyInjection\Compiler\HTMLPurifierPass;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistry;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class HTMLPurifierPassTest extends TestCase
{
    /** @var ContainerBuilder|MockObject */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->createPartialMock(ContainerBuilder::class, [
            'hasAlias',
            'findDefinition',
            'findTaggedServiceIds',
            'getDefinition',
        ]);
    }

    protected function tearDown(): void
    {
        $this->container = null;
    }

    public function testProcessOnlyIfRegistryInterfaceIsDefined()
    {
        $this->container->expects($this->once())
            ->method('hasAlias')
            ->with(HTMLPurifiersRegistryInterface::class)
            ->willReturn(false)
        ;
        $this->container->expects($this->never())
            ->method('findDefinition')
        ;

        $pass = new HTMLPurifierPass();

        $pass->process($this->container);
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $purifier = $container->register(DummyPurifier::class)
            ->addTag('exercise.html_purifier', ['profile' => 'test'])
        ;
        $registry = $container->register('exercise_html_purifier.purifiers_registry', HTMLPurifiersRegistry::class);

        $container->setAlias(HTMLPurifiersRegistryInterface::class, 'exercise_html_purifier.purifiers_registry');

        $pass = new HTMLPurifierPass();
        $pass->process($container);

        $this->assertInstanceOf(Reference::class, $config = $purifier->getArgument(0));
        $this->assertSame('exercise_html_purifier.config.default', (string) $config);
        $this->assertInstanceOf(Definition::class, $locator = $container->findDefinition($registry->getArgument(0)));
        $this->assertArrayHasKey('test', $map = $locator->getArgument(0));
        $this->assertInstanceOf(ServiceClosureArgument::class, $map['test']);
        $this->assertSame(DummyPurifier::class, (string) $map['test']->getValues()[0]);
    }

    public function testProcessDoNothingIfRegistryIsNotDefined()
    {
        $this->container
            ->expects($this->once())
            ->method('hasAlias')
            ->with(HTMLPurifiersRegistryInterface::class)
            ->willReturn(true)
        ;
        $this->container
            ->expects($this->once())
            ->method('findDefinition')
            ->with(HTMLPurifiersRegistryInterface::class)
            ->willThrowException($this->createMock(ServiceNotFoundException::class))
        ;
        $this->container
            ->expects($this->never())
            ->method('findTaggedServiceIds')
        ;

        $pass = new HTMLPurifierPass();
        $pass->process($this->container);
    }

    public function testProcessFailsIfTaggedServiceMissesProfileName()
    {
        $container = new ContainerBuilder();
        $container->register(DummyPurifier::class)
            ->addTag('exercise.html_purifier')
        ;
        $container->register('exercise_html_purifier.purifiers_registry', HTMLPurifiersRegistry::class);
        $container->setAlias(HTMLPurifiersRegistryInterface::class, 'exercise_html_purifier.purifiers_registry');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Tag "exercise.html_purifier" must define a "profile" attribute.');

        $pass = new HTMLPurifierPass();
        $pass->process($container);
    }
}

class DummyPurifier extends \HTMLPurifier
{
}
