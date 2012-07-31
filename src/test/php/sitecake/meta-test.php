<?php
namespace sitecake;

class metaTest extends \PHPUnit_Framework_TestCaseExt {

	static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\io');
		static::mockStaticClass('\\sitecake\\meta');
	}

	function test_path() {
		$GLOBALS['DRAFT_CONTENT_DIR'] = 'draft-dir';
		$this->assertEquals('draft-dir' . DS . '1234.meta', meta::path('1234'));
	}
	
	function test_exists() {
		$GLOBALS['DRAFT_CONTENT_DIR'] = 'draft-dir';
		$map = array(
			array('draft-dir' . DS . '1234.meta', true),
			array('draft-dir' . DS . '1111.meta', false)
		);
		io::staticExpects($this->any())
			->method('file_exists')
			->will($this->returnValueMap($map));
		
		$this->assertTrue(meta::exists('1234'));
		$this->assertFalse(meta::exists('1111'));
	}
	
	function test_get() {
		$GLOBALS['DRAFT_CONTENT_DIR'] = 'draft-dir';
		io::staticExpects($this->any())
			->method('file_get_contents')
			->will($this->returnValue('{"prop1":"val1"}'));
		$this->assertEquals('val1', meta::get('12', 'prop1'));
		$this->assertEquals(array('prop1' => 'val1'), meta::get('13'));	
	}
	
	function test_put() {
		$GLOBALS['DRAFT_CONTENT_DIR'] = 'draft-dir';
		io::staticExpects($this->any())
			->method('file_put_contents')
			->with($this->equalTo('draft-dir' . DS . '123.meta'), 
				$this->equalTo('{"prop1":"val1"}'));
		meta::put('123', array('prop1' => 'val1'));
	}
	
}