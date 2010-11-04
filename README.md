Installation
============

  1. Add this bundle and the HTMLPurifier to your project as Git submodules:

          $ git submodule add git@github.com:Exercise/HTMLPurifierBundle.git src/Bundle/ExerciseCom/HTMLPurifierBundle
          $ git submodule add git://github.com/ezyang/htmlpurifier.git src/vendor/htmlpurifier

  2. Add the `HTMLPurifier.auto.php` class to your project's autoloader bootstrap script:

          // src/autoload.php
          spl_autoload_register(function($class) {
              if ('HTMLPurifier_Config' == $class) {
                  require_once __DIR__ . '/vender/htmlpurifier/library/HTMLPurifier.auto.php';
                  return true;
              }
          });

  3. Add this bundle to your application's kernel:

          // application/ApplicationKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new Bundle\ExerciseCom\HTMLPurifierBundle\HTMLPurifierBundle(),
                  // ...
              );
          }

  4. Configure the `htmlpurifier` service in your config:

          htmlpurifier.config: ~
