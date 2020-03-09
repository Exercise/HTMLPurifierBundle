<?php

namespace Exercise\HTMLPurifierBundle\Form\TypeExtension;

use Symfony\Component\Form\FormTypeExtensionInterface;

if (method_exists(FormTypeExtensionInterface::class, 'getExtendedTypes')) {
    require_once __DIR__.'/forward_compat_trait.inc.php';
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
