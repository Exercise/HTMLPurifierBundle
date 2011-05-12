<?php

namespace Exercise\HTMLPurifierBundle\CacheWarmer;

/**
 * CacheWarmer. HTMLPurifier complains when it cant find the directory in the cache
 * folder so this creates it before hand.
 *
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class SerializerCacheWarmer implements \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface
{
    /**
     * @param string $cacheDir
     */
    public function warmUp($cacheDir)
    {
        $cacheDir = $cacheDir.'/htmlpurifier';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        if (!is_writeable($cacheDir)) {
            chmod($cacheDir, 0777);
        }
    }

    /**
     * @return Boolean
     */
    public function isOptional()
    {
        return false;
    }
}
