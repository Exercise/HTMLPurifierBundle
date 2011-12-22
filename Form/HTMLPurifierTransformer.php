<?php

namespace Exercise\HTMLPurifierBundle\Form;

use Symfony\Component\Form\DataTransformerInterface;

class HTMLPurifierTransformer implements DataTransformerInterface
{
    private $purifier;

    /**
     * Constructor.
     *
     * @param \HTMLPurifier $purifier
     */
    public function __construct(\HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    /**
     * @see Symfony\Component\Form\DataTransformerInterface::transform()
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * @see Symfony\Component\Form\DataTransformerInterface::reverseTransform()
     */
    public function reverseTransform($value)
    {
        return $this->purifier->purify($value);
    }
}
