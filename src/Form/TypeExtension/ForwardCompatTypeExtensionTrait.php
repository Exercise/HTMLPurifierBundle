<?php

namespace Exercise\HTMLPurifierBundle\Form\TypeExtension;

use Symfony\Component\Form\FormTypeExtensionInterface;

if (method_exists(FormTypeExtensionInterface::class, 'getExtendedTypes')) {
    eval('
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
');
} else {
    /**
     * @internal
     */
    trait ForwardCompatTypeExtensionTrait
    {
        /**
         * @return iterable
         */
        private static function doGetExtendedTypes()
        {
        }

        /**
         * @return iterable
         */
        public static function getExtendedTypes()
        {
            return self::doGetExtendedTypes();
        }
    }
}
