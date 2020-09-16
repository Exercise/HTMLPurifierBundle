<?php

namespace Exercise\HTMLPurifierBundle\Twig;

use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Twig\Extension\RuntimeExtensionInterface;

class HTMLPurifierRuntime implements RuntimeExtensionInterface
{
    private $purifiersRegistry;

    public function __construct(HTMLPurifiersRegistryInterface $registry)
    {
        $this->purifiersRegistry = $registry;
    }

    /**
     * Filters the input through an \HTMLPurifier service.
     *
     * @param string|null $string  The html string to purify
     * @param string      $profile A configuration profile name
     *
     * @return string The purified html string
     */
    public function purify(?string $string, string $profile = 'default'): string
    {
        if (null === $string) {
            return '';
        }

        return $this->getHTMLPurifierForProfile($profile)->purify($string);
    }

    /**
     * Gets the HTMLPurifier service corresponding to the given profile.
     *
     * @throws \InvalidArgumentException If the profile does not exist
     */
    private function getHTMLPurifierForProfile(string $profile): \HTMLPurifier
    {
        return $this->purifiersRegistry->get($profile);
    }
}
