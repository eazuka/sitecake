<?php
namespace sitecake;

class utilTest extends \PHPUnit_Framework_TestCaseExt {

	static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\util');
	}

	function test_rpath() {
		$GLOBALS['SC_ROOT'] = '/some/path';
		$this->assertEquals('and/relative/path', 
			util::rpath('/some/path/and/relative/path'));
		$this->assertEquals('/some/other/path',
			util::rpath('/some/other/path'));		
	}
}