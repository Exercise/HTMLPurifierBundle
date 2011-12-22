<?php

namespace Exercise\HTMLPurifierBundle\Tests\Twig;

use Exercise\HTMLPurifierBundle\Twig\HTMLPurifierExtension;

class HTMLPurifierExtensionTest extends \PHPUnit_Framework_TestCase
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

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container->expects($this->once())
            ->method('get')
            ->with('exercise_html_purifier.' . $profile)
            ->will($this->returnValue($purifier));

        $extension = new HTMLPurifierExtension($container);

        $this->assertEquals($purifiedInput, $extension->purify($input, $profile));
    }

    public function providePurifierProfiles()
    {
        return array(
            array('default'),
            array('custom'),
        );
    }
}
