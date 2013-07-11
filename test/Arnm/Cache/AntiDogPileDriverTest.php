<?php
namespace test\Arnm\Cache;

use Arnm\Cache\AntiDogPileDriver;
/**
 * AntiDogPileDriver test case.
 */
class AntiDogPileDriverTest extends \PHPUnit_Framework_TestCase
{

	public function testSave()
	{
		$cache = $this->getMock('Doctrine\Common\Cache\ArrayCache', array(), array(), '', false);
		$wrapper = new AntiDogPileDriver();
	}
}

