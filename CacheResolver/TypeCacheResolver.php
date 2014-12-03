<?php
namespace Weide\AldocBundle\CacheResolver;

class TypeCacheResolver implements CacheResolverInterface
{
    const TTL = 31536000;

    public function isCached($key)
    {
        $filename = $this->getCacheFilename($key);

        if(file_exists($filename))
        {
            if(filemtime($filename) < (time() - self::TTL))
            {
                $this->invalidate($key);
                return false;
            }

            return true;
        }

        return false;
    }

    public function getCached($key)
    {
        if(!$this->isCached($key))
        {
            return false;
        }

        return file_get_contents($this->getCacheFilename($key));
    }

    public function setCache($key, $data)
    {
        return file_put_contents($this->getCacheFilename($key), $data);
    }

    public function invalidate($key)
    {
        if(!$this->isCached($key))
        {
            return false;
        }

        @unlink($this->getCacheFilename($key));
    }

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