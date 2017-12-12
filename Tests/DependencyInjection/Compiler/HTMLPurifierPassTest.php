<?php

namespace Exercise\HTMLPurifierBundle\Tests\DependencyInjection\Compiler;

use Exercise\HTMLPurifierBundle\DependencyInjection\Compiler\HTMLPurifierPass;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistry;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class HTMLPurifierPassTest extends TestCase
{
    public function testProcessOnlyIfRegistryInterfaceIsDefined()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->once())
            ->method('hasAlias')
            ->with(HTMLPurifiersRegistryInterface::class)
            ->willReturn(false)
        ;
        $container->expects($this->never())
            ->method('findDefinition')
        ;

        $pass = new HTMLPurifierPass();

        $pass->process($container);
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
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->once())
            ->method('hasAlias')
            ->with(HTMLPurifiersRegistryInterface::class)
            ->willReturn(true)
        ;
        $container
            ->expects($this->once())
            ->method('findDefinition')
            ->with(HTMLPurifiersRegistryInterface::class)
            ->willThrowException($this->createMock(ServiceNotFoundException::class))
        ;
        $container
            ->expects($this->never())
            ->method('findTaggedServiceIds')
        ;

        $pass = new HTMLPurifierPass();
        $pass->process($container);
    }
}

class DummyPurifier extends \HTMLPurifier {}
