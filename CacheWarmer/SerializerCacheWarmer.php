<?php

namespace Exercise\HTMLPurifierBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Cache warmer for creating HTMLPurifier's cache directory and contents.
 *
 * Run purify() with various contents to have the caches built here, and not
 * on first use, as the owning user may be different then, causing problems
 * with file ownership when deleting the cached files later.
 *
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class SerializerCacheWarmer implements CacheWarmerInterface
{
    private $paths;
    private $htmlPurifier;

    /**
     * @param string[]      $paths
     * @param \HTMLPurifier $htmlPurifier Used to build cache within bundle runtime
     */
    public function __construct(array $paths, \HTMLPurifier $htmlPurifier)
    {
        $this->paths = $paths;
        $this->htmlPurifier = $htmlPurifier;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                if (false === @mkdir($path, 0777, true)) {
                    throw new \RuntimeException(sprintf('Unable to create the HTMLPurifier Serializer cache directory "%s".', $path));
                }
            } elseif (!is_writable($path)) {
                throw new \RuntimeException(sprintf('The HTMLPurifier Serializer cache directory "%s" is not writeable for the current system user.', $path));
            }
        }

        // build htmlPurifier cache for HTML/CSS & URIs with the other Symfony cache warmups.
        // see https://github.com/Exercise/HTMLPurifierBundle/issues/22
        $this->htmlPurifier->purify('<div style="border: thick">-2</div>');
        $this->htmlPurifier->purify('<div style="background:url(\'http://www.example.com/x.gif\');">');
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }
}
