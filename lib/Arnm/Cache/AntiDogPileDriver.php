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
	
	/**
	 * @var Cache
	 */
	private $provider;
	
	/**
	 * {@inheritdoc}
	 * @see Doctrine\Common\Cache.Cache::contains()
	 */
    public function contains($id)
    {
        // TODO Auto-generated method stub
        
    }

	/**
	 * {@inheritdoc}
	 * @see Doctrine\Common\Cache.Cache::delete()
	 */
    public function delete($id)
    {
        // TODO Auto-generated method stub
        
    }

	/**
	 * {@inheritdoc}
	 * @see Doctrine\Common\Cache.Cache::fetch()
	 */
    public function fetch($id)
    {
        // TODO Auto-generated method stub
        
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
    public function save($id, $data, $lifeTime = 0)
    {
		return $this->getProvider()->save($id, $data, $lifeTime);        
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
