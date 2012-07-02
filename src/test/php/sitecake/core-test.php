<?php

namespace sitecake;

class coretest extends \PHPUnit_Framework_TestCaseExt {
	public static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\core');
		static::mockStaticClass('\\sitecake\\util');
	}
		
	public function testDoSomething() {
		core::staticExpects($this->any())
			->method('someMethod')
			->will($this->returnValue('bar'));
		
		util::staticExpects($this->any())
			->method('someUtilFunc')
			->will($this->returnValue('foo'));

		$this->assertEquals('foobar', core::doSomething('a', 'b'));
	}
	
}