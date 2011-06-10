<?php

namespace Exercise\HTMLPurifierBundle\Form;

class HTMLPurifierTransformer implements \Symfony\Component\Form\DataTransformerInterface
{
    protected $purifier;

    public function __construct(\HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        return $this->purifier->purify($value);
    }
}
