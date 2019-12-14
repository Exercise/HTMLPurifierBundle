<?php

namespace Exercise\HTMLPurifierBundle;

interface HTMLPurifiersRegistryInterface
{
    public function has(string $profile): bool;

    public function get(string $profile): \HTMLPurifier;
}
