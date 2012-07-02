<?php
namespace sitecake;

class resourcesTest extends \PHPUnit_Framework_TestCaseExt {
	
	public static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\resources');
	}
	
	public function test_key() {
		resources::$map = array('SOME_TEST_KEY'=>'Some text with {0} {1}');

		$this->assertEquals('Some text with 2 params',
			resources::message('SOME_TEST_KEY', array('2', 'params')));
	}

	public function test_key2() {
		$this->assertEquals('SOME_NONEXISTING_TEST_KEY',
			resources::message('SOME_NONEXISTING_TEST_KEY'));
	}

	public function test_key3() {
		resources::$map = array('SOME_KEY'=>'Some text with {0} {1}');
		
		$this->assertEquals('Some text with {0} param',
			resources::message('SOME_KEY',array(1=>'param')));
	}
	
	public function test_singleParam() {
		resources::$map = array('SOME_KEY'=>'Some text with {0}');
		
		$this->assertEquals('Some text with param',
			resources::message('SOME_KEY', 'param'));
	}
	
}