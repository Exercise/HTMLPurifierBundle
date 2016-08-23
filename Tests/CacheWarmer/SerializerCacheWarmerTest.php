<?php

namespace Exercise\HTMLPurifierBundle\Tests\CacheWarmer;

use Exercise\HTMLPurifierBundle\CacheWarmer\SerializerCacheWarmer;

class SerializerCacheWarmerTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBeRequired()
    {
        $cacheWarmer = new SerializerCacheWarmer(array(), new \HTMLPurifier());
        $this->assertFalse($cacheWarmer->isOptional());
    }

    public function testShouldCreatePaths()
    {
        if (!is_writable(sys_get_temp_dir())) {
            $this->markTestSkipped(sprintf('The system temp directory "%s" is not writeable for the current system user.', sys_get_temp_dir()));
        }

        $path = sys_get_temp_dir() . '/' . uniqid('htmlpurifierbundle');

        $cacheWarmer = new SerializerCacheWarmer(array($path), new \HTMLPurifier());
        $cacheWarmer->warmUp(null);

        $this->assertTrue(is_dir($path));
        $this->assertTrue(is_writeable($path));

        rmdir($path);
    }
}
