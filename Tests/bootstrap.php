<?php

require_once $_SERVER['SYMFONY'] . '/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Exercise\\HTMLPurifierBundle' => '../../',
    'Symfony' => $_SERVER['SYMFONY'],
));
$loader->registerPrefixes(array(
    'HTMLPurifier' => $_SERVER['HTMLPURIFIER'],
));
$loader->register();
