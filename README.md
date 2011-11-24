Installation
============

  1. Add this bundle and the HTMLPurifier to your project as Git submodules:

```
$ git submodule add git://github.com/Exercise/HTMLPurifierBundle.git vendor/bundles/Exercise/HTMLPurifierBundle
$ git submodule add git://github.com/ezyang/htmlpurifier.git vendor/htmlpurifier
```

  2. Add the `HTMLPurifier` prefix to the src/autoload.php file:

``` php
<?php
$loader->registerPrefixes(array(
    // ...
    'HTMLPurifier'    => __DIR__.'/vendor/htmlpurifier/library',
    'Exercise'        => __DIR__.'/vendor/bundles',
    // ...
));
```

  3. Add this bundle to your application's kernel:

``` php
<?php
// application/ApplicationKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
        // ...
    );
}
```

Get it started
--------------------

The bundle provides a [form data transformer](http://symfony.com/doc/2.0/cookbook/form/data_transformers.html)

So the first step is to create form type:

``` php
<?php
namespace FooBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Exercise\HTMLPurifierBundle\Form\HTMLPurifierTransformer;
use HTMLPurifier;

class DescriptionType extends AbstractType
{
    protected $purifier;

    public function __construct(HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->appendClientTransformer(
            new HTMLPurifierTransformer($this->purifier)
        );
    }

    public function getParent(array $options)
    {
        return 'textarea';
    }

    public function getName()
    {
        return 'description';
    }
}
```

Then we should it to the DI container:

``` yml

foo.form.type.description:
    class: FooBundle\Form\Type\DescriptionType
    arguments:
        - '@exercise_html_purifier.default'
    tags:
        - { name: form.type, alias: description }
```

Configuration
--------------------

You can easily change any option of default purifier or create your own. For that just create extension config:

``` yml

exercise_html_purifier:
    default:
        Cache.SerializerPath: '%kernel.cache_dir%/htmlpurifier'
    your_config:
        Core.Encoding: 'ISO-8859-1'
```
