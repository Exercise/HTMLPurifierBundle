<?php

namespace Exercise\HTMLPurifierBundle\Tests\Form;

use Exercise\HTMLPurifierBundle\Form\HTMLPurifierTransformer;
use PHPUnit\Framework\TestCase;

class HTMLPurifierTransformerTest extends TestCase
{
    public function testShouldPurifyOnlyDuringReverseTransform()
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

        $transformer = new HTMLPurifierTransformer($purifier);

        $this->assertEquals($purifiedInput, $transformer->reverseTransform($input));
        $this->assertEquals($purifiedInput, $transformer->transform($purifiedInput));
    }
}
