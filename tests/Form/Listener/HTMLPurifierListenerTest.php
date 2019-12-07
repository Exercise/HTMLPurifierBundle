<?php

namespace Exercise\HTMLPurifierBundle\Tests\Form\Listener;

use Exercise\HTMLPurifierBundle\Form\Listener\HTMLPurifierListener;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class HTMLPurifierListenerTest extends TestCase
{
    public function testPurify()
    {
        $input = 'text';
        $purifiedInput = '<p>text</p>';

        $purifier = $this->createMock('HTMLPurifier');
        $purifier
            ->expects($this->once())
            ->method('purify')
            ->with($input)
            ->willReturn($purifiedInput)
        ;

        $profile = 'test';
        $registry = $this->createMock(HTMLPurifiersRegistryInterface::class);
        $registry
            ->expects($this->once())
            ->method('get')
            ->with($profile)
            ->willReturn($purifier)
        ;

        $listener = new HTMLPurifierListener($registry, $profile);

        $event = $this->getFormEvent($input);

        $listener->purifySubmittedData($event);

        $this->assertSame($purifiedInput, $event->getData());
    }

    public function testPurifyTrimEmptyValues()
    {
        $input = ' ';
        $trimmedInput = '';

        $purifier = $this->createMock('HTMLPurifier');
        $purifier
            ->expects($this->never())
            ->method('purify')
        ;

        $registry = $this->createMock(HTMLPurifiersRegistryInterface::class);
        $registry
            ->expects($this->never())
            ->method('get')
        ;

        $listener = new HTMLPurifierListener($registry, 'test');

        $event = $this->getFormEvent($input);

        $listener->purifySubmittedData($event);

        $this->assertSame($trimmedInput, $event->getData());
    }

    /**
     * @dataProvider provideInvalidInput
     */
    public function testPurifyDoNothingForEmptyOrNonScalarData($input)
    {
        $registry = $this->createMock(HTMLPurifiersRegistryInterface::class);
        $registry
            ->expects($this->never())
            ->method('get')
        ;

        $listener = new HTMLPurifierListener($registry, 'test');

        $event = $this->createMock(FormEvent::class);
        $event
            ->expects($this->once())
            ->method('getData')
            ->willReturn($input)
        ;
        $event
            ->expects($this->never())
            ->method('setData')
        ;

        $listener->purifySubmittedData($event);
    }

    public function provideInvalidInput(): iterable
    {
        yield [''];
        yield [[]];
        yield [new \stdClass()];
    }

    private function getFormEvent($data): FormEvent
    {
        return new FormEvent($this->createMock(FormInterface::class), $data);
    }
}
