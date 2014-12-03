<?php
namespace Weide\AldocBundle\Tests\CacheResolver;
use Weide\AldocBundle\CacheResolver\TypeCacheResolver;

class TypeCacheResolverTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        mkdir(__DIR__.'/../../../../../app/cache/prod/aldoc/type', 0777, true);
    }

    protected function tearDown()
    {
        $this->rrmdir(__DIR__.'/../../../../../app/cache/prod/aldoc');
    }

    public function testGetCacheFilename()
    {
        $pcr = new TypeCacheResolver();
        $this->assertContains('app/cache/prod/aldoc/type/key.xml', $pcr->getCacheFilename('key'));
    }

    public function testGenerateKey()
    {
        $pcr = new TypeCacheResolver();
        $data = array(
            'licenseplate' => '08-TT-NP'
        );
        $this->assertEquals(md5('type-08-TT-NP'), $pcr->generateKey($data));

        $data = array();
        $this->assertFalse($pcr->generateKey($data));
    }

    public function testIsCached()
    {
        $pcr = new TypeCacheResolver();
        $this->assertFalse($pcr->isCached('test'));

        $pcr->setCache('test', 'test');
        $this->assertTrue($pcr->isCached('test'));
    }

    public function testGetCached()
    {
        $pcr = new TypeCacheResolver();
        $this->assertFalse($pcr->getCached('test'));

        $pcr->setCache('test', 'test1234');

        $this->assertEquals('test1234', $pcr->getCached('test'));
    }

    public function testInvalidate()
    {
        $pcr = new TypeCacheResolver();
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