<?php

namespace Exercise\HTMLPurifierBundle\Tests\CacheWarmer;

use Exercise\HTMLPurifierBundle\CacheWarmer\SerializerCacheWarmer;
use PHPUnit\Framework\TestCase;

class SerializerCacheWarmerTest extends TestCase
{
    public function testShouldBeRequired()
    {
        $cacheWarmer = new SerializerCacheWarmer([], new \HTMLPurifier());
        $this->assertFalse($cacheWarmer->isOptional());
    }

    public function testFailsWhenNotWriteable()
    {
        $path = sys_get_temp_dir().'/'.uniqid('htmlpurifierbundle_fails');

        if (false === @mkdir($path, 0000)) {
            $this->markTestSkipped('Tmp dir is not writeable.');
        }

        $this->expectException('RuntimeException');

        $cacheWarmer = new SerializerCacheWarmer([$path], new \HTMLPurifier());
        $cacheWarmer->warmUp(null);

        @rmdir($path);
    }

    public function testShouldCreatePaths()
    {
        if (!is_writable(sys_get_temp_dir())) {
            $this->markTestSkipped(sprintf('The system temp directory "%s" is not writeable for the current system user.', sys_get_temp_dir()));
        }

        $path = sys_get_temp_dir().'/'.uniqid('htmlpurifierbundle');

        $cacheWarmer = new SerializerCacheWarmer([$path], new \HTMLPurifier());
        $cacheWarmer->warmUp(null);

        $this->assertTrue(is_dir($path));
        $this->assertTrue(is_writeable($path));

        rmdir($path);
    }
}
