<?php

namespace Exercise\HTMLPurifierBundle\Form\TypeExtension;

/**
 * @internal
 */
trait ForwardCompatTypeExtensionTrait
{
    private static function doGetExtendedTypes(): iterable
    {
    }

    public static function getExtendedTypes(): iterable
    {
        return self::doGetExtendedTypes();
    }
}
