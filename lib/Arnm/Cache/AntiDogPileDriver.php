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
	 * (non-PHPdoc)
	 * @see Doctrine\Common\Cache.Cache::contains()
	 */
    public function contains($id)
    {
        // TODO Auto-generated method stub
        
    }

	/**
	 * (non-PHPdoc)
	 * @see Doctrine\Common\Cache.Cache::delete()
	 */
    public function delete($id)
    {
        // TODO Auto-generated method stub
        
    }

	/**
	 * (non-PHPdoc)
	 * @see Doctrine\Common\Cache.Cache::fetch()
	 */
    public function fetch($id)
    {
        // TODO Auto-generated method stub
        
    }

	/**
	 * (non-PHPdoc)
	 * @see Doctrine\Common\Cache.Cache::getStats()
	 */
    public function getStats()
    {
        // TODO Auto-generated method stub
        
    }

	/**
	 * (non-PHPdoc)
	 * @see Doctrine\Common\Cache.Cache::save()
	 */
    public function save($id, $data, $lifeTime = 0)
    {
        // TODO Auto-generated method stub
        
    }
}
