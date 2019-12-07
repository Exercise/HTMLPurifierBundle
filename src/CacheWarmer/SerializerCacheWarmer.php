<?php

namespace Exercise\HTMLPurifierBundle\CacheWarmer;

use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Cache warmer for creating HTMLPurifier's cache directory and contents.
 *
 * Create all purifiers to generate their caches here, and not on first use, as
 * the owning user may be different then, causing problems with file ownership
 * when deleting the cached files later.
 *
 * See https://github.com/Exercise/HTMLPurifierBundle/issues/22
 *
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 * @author Jules Pietri <jules@heahprod.com>
 */
class SerializerCacheWarmer implements CacheWarmerInterface
{
    private $paths;
    private $profiles;
    private $registry;
    private $filesystem;

    /**
     * @param string[]                       $paths
     * @param string[]                       $profiles
     * @param HTMLPurifiersRegistryInterface $registry Used to build cache within bundle runtime
     */
    public function __construct(array $paths, array $profiles, HTMLPurifiersRegistryInterface $registry, Filesystem $filesystem)
    {
        $this->paths = $paths;
        $this->profiles = $profiles;
        $this->registry = $registry;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->paths as $path) {
            $this->filesystem->remove($path); // clean previous cache
            $this->filesystem->mkdir($path);
        }

        foreach ($this->profiles as $profile) {
            // Will build the configuration
            $this->registry->get($profile)->purify("<div style=\"background:url('http://www.example.com/x.gif');\">");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }
}
