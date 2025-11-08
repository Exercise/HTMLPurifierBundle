<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use Exercise\HTMLPurifierBundle\CacheWarmer\SerializerCacheWarmer;
use Exercise\HTMLPurifierBundle\Form\TypeExtension\HTMLPurifierTextTypeExtension;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Exercise\HTMLPurifierBundle\Twig\HTMLPurifierExtension;
use Exercise\HTMLPurifierBundle\Twig\HTMLPurifierRuntime;
use Symfony\Component\Form\Extension\Core\Type\TextType;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('exercise_html_purifier.cache_warmer.serializer', SerializerCacheWarmer::class)
            ->tag('kernel.cache_warmer')
            ->args([
                abstract_arg('cache paths'),
                abstract_arg('profiles'),
                service(HTMLPurifiersRegistryInterface::class),
                service('filesystem'),
            ])

        ->set('exercise_html_purifier.form.text_type_extension', HTMLPurifierTextTypeExtension::class)
            ->tag('form.type_extension', ['extended_type' => TextType::class])
            ->args([
                service(HTMLPurifiersRegistryInterface::class),
            ])

        ->set('exercise_html_purifier.twig.extension', HTMLPurifierExtension::class)
            ->tag('twig.extension')

        ->set('exercise_html_purifier.twig.runtime', HTMLPurifierRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service(HTMLPurifiersRegistryInterface::class),
            ]);
};
