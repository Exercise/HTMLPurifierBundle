<?php

namespace Exercise\HTMLPurifierBundle\Tests\Form\DataTransformer;

use Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle;
use Exercise\HTMLPurifierBundle\Form\DataTransformer\HTMLPurifierTransformer;
use HTMLPurifier;
use HTMLPurifier_Config;


class HTMLPurifierTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->bundle = new ExerciseHTMLPurifierBundle();
        $this->bundle->boot();
    }

    public function testShouldPurifyInput()
    {
        $purifier = new HTMLPurifier();
        $purifier->config->set('Cache.DefinitionImpl', null);
        $purifier->config->set('AutoFormat.AutoParagraph', true);

        $transformer = new HTMLPurifierTransformer($purifier);

        $this->assertEquals('<p>text</p>', $transformer->reverseTransform('text'));
    }
}
