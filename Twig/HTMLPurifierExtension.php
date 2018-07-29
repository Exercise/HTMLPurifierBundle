<?php

namespace Exercise\HTMLPurifierBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HTMLPurifierExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('purify', [HTMLPurifierRuntime::class, 'purify'], ['is_safe' => ['html']]),
        );
    }
}
