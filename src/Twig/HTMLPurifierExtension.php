<?php

namespace Exercise\HTMLPurifierBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HTMLPurifierExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('purify', [HTMLPurifierRuntime::class, 'purify'], ['is_safe' => ['html']]),
        ];
    }
}
