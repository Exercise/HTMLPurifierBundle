<?php

namespace Exercise\HTMLPurifierBundle;

interface HTMLPurifiersRegistryInterface
{
    /**
     * @param string $profile
     *
     * @return bool
     */
    public function has($profile);

    /**
     * @param string $profile
     *
     * @return \HTMLPurifier
     */
    public function get($profile);
}
