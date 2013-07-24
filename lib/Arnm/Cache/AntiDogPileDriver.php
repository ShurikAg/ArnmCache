<?php
namespace Arnm\Cache;

use Doctrine\Common\Cache\Cache;
/**
 * This wrapper is built to prevent Dog-pile effect in caching
 *
 * @author Alex Agulyansky <alex@iibspro.com>
 * @copyright Copyright (c) 2013, IIB Solutions Ltd.
 */
class AntiDogPileDriver implements Cache
{
	const STALE_SUFFIX = ':stale';
	const STALE_LIFETIME = 86400;
	const LOCK_SUFFIX = ':lock';
	const LOCK_LIFETIME = 10;

	/**
	 * @var Cache
	 */
	private $provider;

	/**
	 * {@inheritdoc}
	 * @see Doctrine\Common\Cache.Cache::contains()
	 */
    public function contains($key)
    {
        $provider = $this->getProvider();

        $contains = $provider->contains($key);
        //check is anyone trys to regenerate this cache at the moment
        //if not, then the cache HAS in fact expired
        if(!$contains && $this->isLocked($key)) {
            $contains = $provider->contains($this->getStaleKey($key));
        }

        return $contains;
    }

	/**
	 * {@inheritdoc}
	 * @see Doctrine\Common\Cache.Cache::delete()
	 */
    public function delete($key)
    {
        $provider = $this->getProvider();
        $deleted = $provider->delete($key);
        //we do not care if stale was deleted or not in this case
        $provider->delete($this->getStaleKey($key));
        //also make sure that the lock is deleted
        $this->unlock($key);

        return $deleted;
    }

	/**
	 * {@inheritdoc}
	 * @see Doctrine\Common\Cache.Cache::fetch()
	 */
    public function fetch($key)
    {
        $cachedValue = null;

        $provider = $this->getProvider();
        //check if the key is locked
        //if it is, that means that someonce regenerates the cache
        //Stale cache value should be taken
        if($this->isLocked($key)) {
            $cachedValue = $provider->fetch($this->getStaleKey($key));
        } else {
            $cachedValue = $provider->fetch($key);
        }

        return $cachedValue;
    }

	/**
	 * {@inheritdoc}
	 * @see Doctrine\Common\Cache.Cache::getStats()
	 */
    public function getStats()
    {
        return $this->getProvider()->getStats();
    }

	/**
	 * {@inheritdoc}
	 * @see Doctrine\Common\Cache.Cache::save()
	 */
    public function save($key, $data, $lifeTime = 0)
    {
        $provider = $this->getProvider();

        //set main cache
		$saved = $provider->save($key, $data, $lifeTime);
		$staleSaved = false;
		if($saved) {
		    $staleSaved = $provider->save($this->getStaleKey($key), $data, $this->getStaleLifetime($lifeTime));
		}

		//small backup plan
		//if something has not been saved we delete everything and letting the client know about the failure
		if(!$saved || !$staleSaved) {
		    $this->delete($key);
		    return false;
		}

		//if everything is OK, make sure that we unlocked the key
		$this->unlock($key);

		return true;
    }

    /**
     * Provide defaul proxy methid for allthe rest of the method that have not been specifically implemented
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
    	$provider = $this->getProvider();
    	if(!method_exists($provider, $name))
    	{
    		throw new \RuntimeException("Call to undefined method ".__CLASS__.":".$name);
    	}

    	return call_user_func_array(array($provider, $name), $arguments);
    }

    /**
     * Checks if the cache regeneration for this key is locked.
     *
     * @param string $key
     *
     * @return boolean
     */
    public function isLocked($key)
    {
        return $this->getProvider()->contains($this->getLockKey($key));
    }

    /**
     * Lockes the key for limited time
     *
     * @param string $key
     *
     * @return boolean
     */
    public function lock($key)
    {
        return $this->getProvider()->save($this->getLockKey($key), 1, self::LOCK_LIFETIME);
    }

    /**
     * Unlockes the key
     *
     * @param string $key
     *
     * @return boolean
     */
    public function unlock($key)
    {
        return $this->getProvider()->delete($this->getLockKey($key));
    }

    /**
     * Creates a cache ley for stale cache
     *
     * @param string $key
     */
    protected function getStaleKey($key)
    {
        return ((string) $key.self::STALE_SUFFIX);
    }

    /**
     * Calculates the lifetime for stale cache
     *
     * @param int $lifetime
     *
     * @return int
     */
    protected function getStaleLifetime($lifetime)
    {
        return ((int) ($lifetime+self::STALE_LIFETIME));
    }

    /**
     * Creates a cache ley for locking
     *
     * @param string $key
     */
    protected function getLockKey($key)
    {
        return ((string) $key.self::LOCK_SUFFIX);
    }

    /**
     * Sets cache provider
     *
     * @param Cache $cache
     */
    public function setProvider(Cache $cache)
    {
    	$this->provider = $cache;
    }

    /**
     * Gets cache provider object
     *
     * @return Cache
     */
    public function getProvider()
    {
    	return $this->provider;
    }
}
