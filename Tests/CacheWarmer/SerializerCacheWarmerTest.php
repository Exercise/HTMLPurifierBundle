<?php

namespace Exercise\HTMLPurifierBundle\Tests\CacheWarmer;

use Exercise\HTMLPurifierBundle\CacheWarmer\SerializerCacheWarmer;

class SerializerCacheWarmerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->cacheWarmer = new SerializerCacheWarmer();
    }

    public function testIsMandatory()
    {
        $this->assertFalse($this->cacheWarmer->isOptional());
    }

    public function testWarmUp()
    {
        $dir = sys_get_temp_dir() . uniqid('htmlpurifierbundle');
        $this->assertFalse(is_dir($dir . '/htmlpurifier'));

        $this->cacheWarmer->warmUp($dir);
        $this->assertTrue(is_dir($dir . '/htmlpurifier'));
        $this->assertTrue(is_writeable($dir . '/htmlpurifier'));
    }
}
