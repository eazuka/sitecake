<?php
namespace sitecake;

class upgradeTest extends \PHPUnit_Framework_TestCaseExt {

	public static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\upgrade');
		static::mockStaticClass('\\sitecake\\io');
	}

	public function test_version() {
		$this->assertEquals(2001001, upgrade::version('2.1.1'));
		$this->assertEquals(2100001, upgrade::version('2.100.1'));
		$this->assertEquals(2000000, upgrade::version('2.0.0'));
		$this->assertEquals(-1, upgrade::version('2a.1.1'));
	}
	
	public function test_latest_local() {
		io::staticExpects($this->any())
              ->method('glob')
              ->will($this->returnValue(
				array('1.0.3', '..', 'test', '1.0.4', '2.0.1')));
		$this->assertEquals(2000001, upgrade::latest_local());		
	}
	
	public function test_to_version() {
		$this->assertEquals('2.0.1', upgrade::to_version(2000001));
		$this->assertEquals('0.0.1', upgrade::to_version(1));
		$this->assertEquals('0.1.0', upgrade::to_version(1000));
	}
}