<?php

namespace Exercise\HTMLPurifierBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use HTMLPurifier;

/**
 * Cache warmer for creating HTMLPurifier's cache directory.
 *
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class SerializerCacheWarmer implements CacheWarmerInterface
{
    private $paths;

    /**
     * Constructor.
     *
     * @param array $paths
     */
    public function __construct(array $paths, HTMLPurifier $htmlPurifier)
    {
        $this->paths = $paths;
        $this->htmlPurifier = $htmlPurifier;
    }

    /**
     * @see Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface::warmUp()
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
        $this->htmlPurifier->purify('<div style="border: thick">-2</div>');
        $this->htmlPurifier->purify('<div style="background:url(\'http://www.example.com/x.gif\');">');
    }

    /**
     * @see Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface::isOptional()
     */
    public function isOptional()
    {
        return false;
    }
}
