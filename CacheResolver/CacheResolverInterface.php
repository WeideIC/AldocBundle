<?php
namespace Weide\AldocBundle\CacheResolver;

interface CacheResolverInterface
{
    public function isCached($key);
    public function getCached($key);
    public function setCache($key, $data);
    public function invalidate($key);
    public function generateKey(array $data);
}