<?php
namespace test\Arnm\Cache;

use Arnm\Cache\AntiDogPileDriver;
/**
 * AntiDogPileDriver test case.
 */
class AntiDogPileDriverTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Tests save and getStats methods
	 */
    public function testSaveAndGetStats()
    {
        $cache = $this->getMock('Doctrine\Common\Cache\ArrayCache', array('save', 'delete', 'getStats'), array(), '', false);
        $cache->expects($this->at(0))
            ->method('save')
            ->with($this->equalTo('key'), $this->equalTo('value'), 30)
            ->will($this->returnValue(true));
        $cache->expects($this->at(1))
            ->method('save')
            ->with($this->equalTo('key'.AntiDogPileDriver::STALE_SUFFIX), $this->equalTo('value'), (30+AntiDogPileDriver::STALE_LIFETIME))
            ->will($this->returnValue(true));
        $cache->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('key'.AntiDogPileDriver::LOCK_SUFFIX))
            ->will($this->returnValue(true));
        $cache->expects($this->once())
            ->method('getStats')
            ->will($this->returnValue('stats'));

        $wrapper = new AntiDogPileDriver();
        $wrapper->setProvider($cache);
        $this->assertTrue($wrapper->save('key', 'value', 30));
        $this->assertEquals('stats', $wrapper->getStats());
    }

    /**
     * Tests proxying undefined methods
     */
    public function test__Call()
    {
    	$cache = $this->getMock('Doctrine\Common\Cache\ArrayCache', array('call'), array(), '', false);
        $cache->expects($this->once())
            ->method('call')
            ->with($this->equalTo('input'))
            ->will($this->returnValue('called'));

        $wrapper = new AntiDogPileDriver();
        $wrapper->setProvider($cache);
        $this->assertEquals('called', $wrapper->call('input'));

        try {
        	$wrapper->hello();
        	$this->fail("Should through RuntimeException if the method is not defined!");
        } catch (\RuntimeException $e){

        }
    }

    public function testDelete()
    {
        $cache = $this->getMock('Doctrine\Common\Cache\ArrayCache', array('delete'), array(), '', false);
        $cache->expects($this->at(0))
            ->method('delete')
            ->with($this->equalTo('key'))
            ->will($this->returnValue(true));
        $cache->expects($this->at(1))
            ->method('delete')
            ->with($this->equalTo('key'.AntiDogPileDriver::STALE_SUFFIX))
            ->will($this->returnValue(true));
        $cache->expects($this->at(2))
            ->method('delete')
            ->with($this->equalTo('key'.AntiDogPileDriver::LOCK_SUFFIX))
            ->will($this->returnValue(true));
        $cache->expects($this->at(3))
            ->method('delete')
            ->with($this->equalTo('key'))
            ->will($this->returnValue(false));
        $cache->expects($this->at(4))
            ->method('delete')
            ->with($this->equalTo('key'.AntiDogPileDriver::STALE_SUFFIX))
            ->will($this->returnValue(true));
        $cache->expects($this->at(5))
            ->method('delete')
            ->with($this->equalTo('key'.AntiDogPileDriver::LOCK_SUFFIX))
            ->will($this->returnValue(true));

        $wrapper = new AntiDogPileDriver();
        $wrapper->setProvider($cache);
        $this->assertTrue($wrapper->delete('key'));
        $this->assertFalse($wrapper->delete('key'));
    }

    public function testContains()
    {
        $cache = $this->getMock('Doctrine\Common\Cache\ArrayCache', array('contains'), array(), '', false);
        //in case that the main one exists and valid
        $cache->expects($this->at(0))
            ->method('contains')
            ->with($this->equalTo('key'))
            ->will($this->returnValue(true));
        //in case that the main has expired and there is nobody else regenerating the cache
        $cache->expects($this->at(1))
            ->method('contains')
            ->with($this->equalTo('key'))
            ->will($this->returnValue(false));
        $cache->expects($this->at(2))
            ->method('contains')
            ->with($this->equalTo('key'.AntiDogPileDriver::LOCK_SUFFIX))
            ->will($this->returnValue(false));
        //in case that the main has expired, there is somebody else trying to regenerate the cache and stale still valid
        $cache->expects($this->at(3))
            ->method('contains')
            ->with($this->equalTo('key'))
            ->will($this->returnValue(false));
        $cache->expects($this->at(4))
            ->method('contains')
            ->with($this->equalTo('key'.AntiDogPileDriver::LOCK_SUFFIX))
            ->will($this->returnValue(true));
        $cache->expects($this->at(5))
            ->method('contains')
            ->with($this->equalTo('key'.AntiDogPileDriver::STALE_SUFFIX))
            ->will($this->returnValue(true));

        $wrapper = new AntiDogPileDriver();
        $wrapper->setProvider($cache);
        //in case that the main one exists and valid
        $this->assertTrue($wrapper->contains('key'));
        //in case that the main has expired and there is nobody else regenerating the cache
        $this->assertFalse($wrapper->contains('key'));
        //in case that the main has expired, there is somebody else trying to regenerate the cache and stale still valid
        //in this case we expect to get the stale value and let the one that regenerates the cache finish his job
        $this->assertTrue($wrapper->contains('key'));
    }

    public function testLockUnlock()
    {
        $cache = $this->getMock('Doctrine\Common\Cache\ArrayCache', array('save', 'delete'), array(), '', false);
        $cache->expects($this->once())
            ->method('save')
            ->with($this->equalTo('lock'.AntiDogPileDriver::LOCK_SUFFIX), $this->equalTo(1), $this->equalTo(AntiDogPileDriver::LOCK_LIFETIME))
            ->will($this->returnValue(true));
        $cache->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('lock'.AntiDogPileDriver::LOCK_SUFFIX))
            ->will($this->returnValue(true));

        $wrapper = new AntiDogPileDriver();
        $wrapper->setProvider($cache);

        $wrapper->lock('lock');
        $wrapper->unlock('lock');
    }
}

