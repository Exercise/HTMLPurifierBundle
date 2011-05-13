Installation
============

  1. Add this bundle and the HTMLPurifier to your project as Git submodules:

          $ git submodule add git://github.com/Exercise/HTMLPurifierBundle.git vendor/bundles/Exercise/HTMLPurifierBundle
          $ git submodule add git://github.com/ezyang/htmlpurifier.git vendor/htmlpurifier

  2. Add the `HTMLPurifier` prefix to the src/autoload.php file:

        $loader->registerPrefixes(array(
            // ...
            'HTMLPurifier'    => __DIR__.'/vendor/htmlpurifier/library',
            // ...
        ));


  3. Add this bundle to your application's kernel:

          // application/ApplicationKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
                  // ...
              );
          }

Cache.SerializerPath
--------------------

When using HTMLPurifier caching HTMLPurifier complains when the dir isnt availible therefor this
bundle includes a CacheWarmer that will create a `%kernel.cache_dir%/htmlpurifier`
directory to prevent this. But this **require** the config for caching be set as follows.

``` yml
exercise_html_purifier:
    your_config_name:
        Cache.SerializerPath: %kernel.cache_dir%/htmlpurifier
```
