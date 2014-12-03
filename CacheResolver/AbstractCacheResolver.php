<?php
namespace Weide\AldocBundle\CacheResolver;

abstract class AbstractCacheResolver
{
    const TTL = 0;

    public function isCached($key)
    {
        $filename = $this->getCacheFilename($key);

        if(file_exists($filename))
        {
            if(filemtime($filename) < (time() - static::TTL))
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

        return unlink($this->getCacheFilename($key));
    }

    abstract public function generateKey(array $data);
    abstract public function getCacheFilename($key);
}