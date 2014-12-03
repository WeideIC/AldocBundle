<?php
namespace Weide\AldocBundle\CacheResolver;

class TypeCacheResolve extends AbstractCacheResolver
{
    const TTL = 31536000;

    public function generateKey(array $data)
    {
        if(!array_key_exists('licenseplate', $data))
        {
            return false;
        }

        return md5('type-' . $data['licenseplate']);
    }

    public function getCacheFilename($key)
    {
        return __DIR__.'/../../../../app/cache/prod/aldoc/type/' . $key . '.xml';
    }
}