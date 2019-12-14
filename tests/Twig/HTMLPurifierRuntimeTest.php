<?php

namespace Exercise\HTMLPurifierBundle\Tests\Twig;

use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Exercise\HTMLPurifierBundle\Twig\HTMLPurifierRuntime;
use PHPUnit\Framework\TestCase;

class HTMLPurifierRuntimeTest extends TestCase
{
    /**
     * @dataProvider providePurifierProfiles
     */
    public function testPurifyFilter($profile)
    {
        $input = 'text';
        $purifiedInput = '<p>text</p>';

        $purifier = $this->getMockBuilder('HTMLPurifier')
            ->disableOriginalConstructor()
            ->getMock();

        $purifier->expects($this->once())
            ->method('purify')
            ->with($input)
            ->will($this->returnValue($purifiedInput));

        $registry = $this->createMock(HTMLPurifiersRegistryInterface::class);

        $registry->expects($this->once())
            ->method('get')
            ->with($profile)
            ->will($this->returnValue($purifier))
        ;

        $extension = new HTMLPurifierRuntime($registry);

        $this->assertEquals($purifiedInput, $extension->purify($input, $profile));
    }

    public function providePurifierProfiles(): iterable
    {
        yield ['default'];
        yield ['custom'];
    }
}
