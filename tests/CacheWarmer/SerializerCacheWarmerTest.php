<?php

namespace Exercise\HTMLPurifierBundle\Tests\CacheWarmer;

use Exercise\HTMLPurifierBundle\CacheWarmer\SerializerCacheWarmer;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class SerializerCacheWarmerTest extends TestCase
{
    public function testShouldBeRequired(): void
    {
        $cacheWarmer = new SerializerCacheWarmer([], [], $this->createMock(HTMLPurifiersRegistryInterface::class), new Filesystem());

        $this->assertFalse($cacheWarmer->isOptional());
    }

    public function testWarmUpShouldCreatePaths(): void
    {
        $fs = new Filesystem();
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'html_purifier';

        if ($fs->exists($path)) {
            $fs->remove($path);
        }

        $this->assertFalse($fs->exists($path));

        $cacheWarmer = new SerializerCacheWarmer([$path], [], $this->createMock(HTMLPurifiersRegistryInterface::class), $fs);
        $cacheWarmer->warmUp('');

        $this->assertTrue($fs->exists($path));

        $fs->remove($path);
    }

    public function testWarmUpShouldCallPurifyForEachProfile(): void
    {
        $purifier = $this->createMock(\HTMLPurifier::class);
        $purifier->expects($this->exactly(2))
            ->method('purify')
        ;

        $registry = $this->createMock(HTMLPurifiersRegistryInterface::class);
        $registry->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['first'], ['second'])
            ->willReturn($purifier)
        ;

        $cacheWarmer = new SerializerCacheWarmer([], ['first', 'second'], $registry, new Filesystem());
        $cacheWarmer->warmUp('');
    }
}
