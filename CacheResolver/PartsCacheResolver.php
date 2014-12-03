<?php
namespace Weide\AldocBundle\CacheResolver;

class PartsCacheResolver extends AbstractCacheResolver
{
    const TTL = 86400;

    public function generateKey(array $data)
    {
        if(!array_key_exists('menucode', $data) || !array_key_exists('typecode', $data))
        {
            return false;
        }

        return md5('parts-' . $data['menucode'] . '-' . $data['typecode']);
    }

    public function getCacheFilename($key)
    {
        return __DIR__.'/../../../../app/cache/prod/aldoc/parts/' . $key . '.xml';
    }
}