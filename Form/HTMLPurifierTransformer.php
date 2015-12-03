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
     * @param mixed $value
     * @return mixed
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * @see Symfony\Component\Form\DataTransformerInterface::reverseTransform()
     * @param mixed $value
     * @return mixed|string
     */
    public function reverseTransform($value)
    {
        return $this->purifier->purify($value);
    }
}
