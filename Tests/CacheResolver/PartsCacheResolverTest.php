<?php
namespace Weide\AldocBundle\Tests\CacheResolver;
use Weide\AldocBundle\CacheResolver\PartsCacheResolver;

class PartsCacheResolverTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        mkdir(__DIR__.'/../../../../../app/cache/prod/aldoc/parts', 0777, true);
    }

    protected function tearDown()
    {
        $this->rrmdir(__DIR__.'/../../../../../app/cache/prod/aldoc');
    }

    public function testGetCacheFilename()
    {
        $pcr = new PartsCacheResolver();
        $this->assertContains('app/cache/prod/aldoc/parts/key.xml', $pcr->getCacheFilename('key'));
    }

    public function testGenerateKey()
    {
        $pcr = new PartsCacheResolver();
        $data = array(
            'menucode' => 5,
            'typecode' => 1234
        );
        $this->assertEquals(md5('type-5-1234'), $pcr->generateKey($data));

        $data = array(
            'typecode' => 1234
        );
        $this->assertFalse($pcr->generateKey($data));

        $data = array(
            'menucode' => 5
        );
        $this->assertFalse($pcr->generateKey($data));
    }

    public function testIsCached()
    {
        $pcr = new PartsCacheResolver();
        $this->assertFalse($pcr->isCached('test'));

        $pcr->setCache('test', 'test');
        $this->assertTrue($pcr->isCached('test'));
    }

    public function testGetCached()
    {
        $pcr = new PartsCacheResolver();
        $this->assertFalse($pcr->getCached('test'));

        $pcr->setCache('test', 'test1234');

        $this->assertEquals('test1234', $pcr->getCached('test'));
    }

    public function testInvalidate()
    {
        $pcr = new PartsCacheResolver();
        $this->assertFalse($pcr->invalidate('test'));

        $pcr->setCache('test', 'test1234');
        $this->assertTrue($pcr->invalidate('test'));
    }

    public function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}