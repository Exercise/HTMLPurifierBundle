[![Total Downloads](https://poser.pugx.org/exercise/htmlpurifier-bundle/downloads)](https://packagist.org/packages/exercise/htmlpurifier-bundle)
[![Latest Stable Version](https://poser.pugx.org/exercise/htmlpurifier-bundle/v/stable)](https://packagist.org/packages/exercise/htmlpurifier-bundle)
[![License](https://poser.pugx.org/exercise/htmlpurifier-bundle/license)](https://packagist.org/packages/exercise/htmlpurifier-bundle)
[![Build Status](https://travis-ci.org/Exercise/HTMLPurifierBundle.svg?branch=master)](https://travis-ci.org/Exercise/HTMLPurifierBundle)

# ExerciseHTMLPurifierBundle

This bundle integrates [HTMLPurifier][] into Symfony.

  [HTMLPurifier]: http://htmlpurifier.org/

## Installation

## Symfony 3.4 and above (using Composer)

Require the bundle in your composer.json file:

```json
{
    "require": {
        "exercise/htmlpurifier-bundle": "*"
    }
}
```

Install the bundle:

```bash
$ composer require exercise/htmlpurifier-bundle
```

Register the bundle in Symfony 3:

```php
// app/AppKernel.php

public function registerBundles()
{
    return [
        // ...
        new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
    ];
}
```

## Configuration in Symfony 3

The configuration is the same as the following section, but the path should be
`app/config.yml` instead.

## Configuration in Symfony 4 and up

If you do not explicitly configure this bundle, an HTMLPurifier service will be
defined as `exercise_html_purifier.default`. This behavior is the same as if you
had specified the following configuration:

```yaml
# config/packages/exercise_html_purifier.yaml

exercise_html_purifier:
    default_cache_serializer_path: '%kernel.cache_dir%/htmlpurifier'
```

The `default` profile is special, it is *always* defined and its configuration
is inherited by all custom profiles.
`exercise_html_purifier.default` is the default service using the base
configuration.

```yaml
# config/packages/exercise_html_purifier.yaml

exercise_html_purifier:
    default_cache_serializer_path: '%kernel.cache_dir%/htmlpurifier'
    html_profiles:
        custom:
            config:
                Core.Encoding: 'ISO-8859-1'
                HTML.Allowed: 'a[href|target],p,br'
                Attr.AllowedFrameTargets: '_blank'
```

In this example, a `exercise_html_purifier.custom` service will also be defined,
which includes cache, encoding, HTML tags and attributes options. Available configuration
options may be found in HTMLPurifier's [configuration documentation][].

**Note:** If you define a `default` profile but omit `Cache.SerializerPath`, it
will still default to the path above. You can specify a value of `null` for the
option to suppress the default path.

  [configuration documentation]: http://htmlpurifier.org/live/configdoc/plain.html

## Autowiring

By default type hinting `\HtmlPurifier` in your services will autowire
the `exercise_html_purifier.default` service.
To override it and use your own config as default autowired services just add
this configuration:

```yaml
# config/services.yaml
services:
    #...
    
    exercise_html_purifier.default: '@exercise_html_purifier.custom'
```

### Using a custom purifier class as default

If you want to use your own class as default purifier, define the new alias as
below:

```yaml
# config/services.yaml
services:
    # ...

    exercise_html_purifier.default: '@App\Html\CustomHtmlPurifier'
```

### Argument binding (Symfony >= 4.4)

The bundle also leverages the alias argument binding for each profile. So the
following config:

```yaml
    html_profiles:
        blog:
            # ...
        gallery:
            # ...
```

will register the following binding:

```php
 // default config is bound whichever argument name is used
public function __construct(\HTMLPurifier $purifier) {}
public function __construct(\HTMLPurifier $htmlPurifier) {}
public function __construct(\HTMLPurifier $blogPurifier) {} // blog config
public function __construct(\HTMLPurifier $galleryPurifier) {} // gallery config
```

## Form Type Extension

This bundles provides a form type extension for filtering form fields with
HTMLPurifier. Purification is done early during the PRE_SUBMIT event, which
means that client data will be filtered before being bound to the form.

Two options are automatically available in all `TextType` based types:

```php
<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, ['purify_html' => true]) // will use default profile 
            ->add('sneek_peak', TextType::class, ['purify_html' => true, 'purify_html_profile' => 'sneak_peak'])
            // ...
        ;
    }
    
    // ...
}
```

Every type extending `TextType` (i.e: `TextareaType`) inherit these options.
It also means that if you use a type such as [CKEditorType][], you will benefit
from these options without configuring anything.

  [CKEDitorType]: https://github.com/egeloen/IvoryCKEditorBundle/blob/master/Form/Type/CKEditorType.php#L570

## Twig Filter

This bundles registers a `purify` filter with Twig. Output from this filter is
marked safe for HTML, much like Twig's built-in escapers. The filter may be used
as follows:

```twig
{# Filters text's value through the "default" HTMLPurifier service #}
{{ text|purify }}

{# Filters text's value through the "custom" HTMLPurifier service #}
{{ text|purify('custom') }}
```

## Purifiers Registry

A `Exercise\HtmlPurifierBundle\HtmlPurifiersRegistry` class is registered by default
as a service. To add your custom instance of purifier, and make it available to
the form type and Twig extensions through its profile name, you can use the tag
`exercise.html_purifier` as follow:

```yaml
# config/services.yaml

services:
    # ...
    
    App\HtmlPurifier\CustomPurifier:
        tags:
            - name: exercise.html_purifier
              profile: custom
```

Now your purifier can be used when:

```php
// In a form type
$builder
    ->add('content', TextareaType::class, [
        'purify_html' => true,
        'purify_html_profile' => 'custom',
    ])
    // ...
```

```twig
{# in a template #}
{{ html_string|purify('custom') }}
```

## How to Customize a Config Definition

### Whitelist Attributes

In some case, you might want to set some rules for a specific tag.
This is what the following config is about:

```yaml
# config/packages/exercise_html_purifier.yaml
exercise_html_purifier:
    html_profiles:
        default:
            config:
                HTML.Allowed: <
                    *[id|class|name],
                    a[href|title|rel|target],
                    img[src|alt|height|width],
                    br,div,embed,object,u,em,ul,ol,li,strong,span
            attributes:
                img:
                    # attribute name, type (Integer, Color, ...)
                    data-id: ID
                    data-image-size: Text
                span:
                    data-link: URI
```

See [HTMLPurifier_AttrTypes][] for more options.

  [HTMLPurifier_AttrTypes]: https://github.com/ezyang/htmlpurifier/blob/master/library/HTMLPurifier/AttrTypes.php

### Whitelist Elements

In some case, you might want to set some rules for a specific tag.
This is what the following config is about:

```yaml
# config/packages/exercise_html_purifier.yaml
exercise_html_purifier:
    html_profiles:
        default:
            # ...
            elements:
                video:
                    - Block
                    - 'Optional: (source, Flow) | (Flow, source) | Flow'
                    - Common # allows a set of common attributes
                    # The 4th and 5th arguments are optional
                    - src: URI # list of type rules by attributes
                      type: Text
                      width: Length
                      height: Length
                      poster: URI
                      preload: 'Enum#auto,metadata,none'
                      controls: Bool
                source:
                    - Block
                    - Flow
                    - Common
                    - { src: URI, type: Text }
                    - [style] # list of forbidden attributes
```

Would be equivalent to:

```php
$def = $config->getHTMLDefintion(true);
$def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
    'src' => 'URI',
    'type' => 'Text',
    'width' => 'Length',
    'height' => 'Length',
    'poster' => 'URI',
    'preload' => 'Enum#auto,metadata,none',
    'controls' => 'Bool',
]);
$source = $def->addElement('source', 'Block', 'Flow', 'Common', [
    'src' => 'URI',
    'type' => 'Text',
]);
$source->excludes = ['style' => true];
```

See [HTMLPurifier documentation][] for more details.

  [HTMLPurifier documentation]: http://htmlpurifier.org/docs/enduser-customize.html

### Blank Elements

It might happen that you need a tag clean from any attributes.
Then just add it to the list:

```yaml
# config/packages/exercise_html_purifier.yaml
exercise_html_purifier:
    html_profiles:
        default:
            # ...
            blank_elements: [legend, figcaption]
```

## How to Reuse Profiles

What can really convenient is to reuse some profile definition
to build other custom definitions.

```yaml
# config/packages/exercise_html_purifier.yaml
exercise_html_purifier:
    html_profiles:
        base:
            # ...
        video:
            # ...
        all:
            parents: [base, video]
```

In this example the profile named "all" will inherit the "default" profile,
then the two custom ones. The order is important as each profile overrides the
previous, and "all" could define its own rules too.

## Contributing

PRs are welcomed :). Please target the `2.0` branch for bug fixes and `master`
for new features.
