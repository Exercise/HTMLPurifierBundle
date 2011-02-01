<?php

namespace Bundle\ExerciseCom\HTMLPurifierBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HTMLPurifierBundle extends Bundle
{
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return strtr(__DIR__, '\\', '/');
    }
}
