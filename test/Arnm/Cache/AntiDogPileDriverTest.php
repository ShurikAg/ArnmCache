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
        $cache = $this->getMock('Doctrine\Common\Cache\ArrayCache', array('save', 'getStats'), array(), '', false);
        $cache->expects($this->once())
            ->method('save')
            ->with($this->equalTo('key'), $this->equalTo('value'), 30)
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
}

