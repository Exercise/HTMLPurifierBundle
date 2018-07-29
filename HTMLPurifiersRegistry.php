<?php

namespace Exercise\HTMLPurifierBundle;

use Psr\Container\ContainerInterface;

class HTMLPurifiersRegistry implements HTMLPurifiersRegistryInterface
{
    private $purifiersLocator;

    public function __construct(ContainerInterface $purifiersLocator)
    {
        $this->purifiersLocator = $purifiersLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function has($profile)
    {
        return $this->purifiersLocator->has($profile);
    }

    /**
     * {@inheritdoc}
     */
    public function get($profile)
    {
        return $this->purifiersLocator->get($profile);
    }
}
