<?php

namespace Exercise\HTMLPurifierBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ExerciseHTMLPurifierBundle extends Bundle
{
    public function boot()
    {
        new \HTMLPurifier_Bootstrap();
    }
}
