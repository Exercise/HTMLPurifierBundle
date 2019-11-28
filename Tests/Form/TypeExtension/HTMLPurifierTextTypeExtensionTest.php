<?php

namespace Exercise\HTMLPurifierBundle\Tests\Form\TypeExtension;

use Exercise\HTMLPurifierBundle\Form\Listener\HTMLPurifierListener;
use Exercise\HTMLPurifierBundle\Form\TypeExtension\HTMLPurifierTextTypeExtension;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class HTMLPurifierTextTypeExtensionTest extends FormIntegrationTestCase
{
    private $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(HTMLPurifiersRegistryInterface::class);

        parent::setUp();
    }

    protected function getTypeExtensions()
    {
        return [
            new HTMLPurifierTextTypeExtension($this->registry),
        ];
    }

    public function testDefaultOptions()
    {
        $this->registry
            ->expects($this->never())
            ->method('has')
        ;
        $this->registry
            ->expects($this->never())
            ->method('get')
        ;

        $form = $this->factory->create(TextType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertFalse($options['purify_html']);
        $this->assertNull($options['purify_html_profile']);
        $this->assertTrue($options['trim']);
        $this->assertFalse($this->hasPurifierListener($form));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @expectedExceptionMessage The profile "default" is not registered.
     */
    public function testPurifyOptionsNeedDefaultProfile()
    {
        $this->registry
            ->expects($this->once())
            ->method('has')
            ->with('default')
            ->willReturn(false)
        ;
        $this->registry
            ->expects($this->never())
            ->method('get');
        ;

        $this->factory->create(TextType::class, null, ['purify_html' => true]);
    }

    public function testDefaultOptionsWhenPurifyIsTrue()
    {
        $this->registry
            ->expects($this->once())
            ->method('has')
            ->with('default')
            ->willReturn(true)
        ;

        $form = $this->factory->create(TextType::class, null, ['purify_html' => true]);
        $options = $form->getConfig()->getOptions();

        $this->assertTrue($options['purify_html']);
        $this->assertSame('default', $options['purify_html_profile']);
        $this->assertFalse($options['trim']);
        $this->assertTrue($this->hasPurifierListener($form));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @expectedExceptionMessage The profile "test" is not registered.
     */
    public function testInvalidProfile()
    {
        $this->registry
            ->expects($this->once())
            ->method('has')
            ->with('test')
            ->willReturn(false)
        ;
        $this->registry
            ->expects($this->never())
            ->method('get')
        ;

        $this->factory->create(TextType::class, null, [
            'purify_html' => true,
            'purify_html_profile' => 'test',
        ]);
    }

    /**
     * @param FormInterface $form
     *
     * @return bool
     */
    private function hasPurifierListener(FormInterface $form)
    {
        foreach ($form->getConfig()->getEventDispatcher()->getListeners(FormEvents::PRE_SUBMIT) as $listener) {
            if ($listener[0] instanceof HTMLPurifierListener) {
                return true;
            }
        }

        return false;
    }
}
