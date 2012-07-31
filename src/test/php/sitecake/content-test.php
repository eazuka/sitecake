<?php
namespace sitecake;

class contentTest extends \PHPUnit_Framework_TestCaseExt {

	static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\content');
	}

	function test_process_save1() {
		$oldHtml = '';
		$newHtml = '';
		$this->assertEquals('', content::process_save($oldHtml, $newHtml));
	}

	function test_process_save2() {
		$oldHtml = '<p>text</p>' . 
			'<img src="images/123.jpg" data="0:0:200:200:0:0:200:200" />';
		$newHtml = '<p>text</p>';
		$this->assertEquals('<p>text</p>', 
			content::process_save($oldHtml, $newHtml));
	}

	function test_process_save3() {
		content::staticExpects($this->any())
			->method('process_image')
			->will($this->returnValue('sitecake-content/432.jpg'));
		
		$oldHtml = '<p>text</p>';
		$newHtml = '<p>text</p>' . 
			'<img src="images/123.jpg" data="0:0:200:200:0:0:200:200"/>';
		$this->assertEquals('<p>text</p><img src="sitecake-content/432.jpg" ' .
			'data="0:0:200:200:0:0:200:200" />', 
			content::process_save($oldHtml, $newHtml));
	}
	
	function test_process_save4() {
		$oldHtml = 
			'<img src="images/123.jpg" data="0:0:200:200:0:0:200:200" />';
		$newHtml = '<p>text</p>' .
				'<img src="images/123.jpg" data="0:0:200:200:0:0:200:200" />';
		$this->assertEquals('<p>text</p><img src="images/123.jpg" ' .
				'data="0:0:200:200:0:0:200:200" />', 
		content::process_save($oldHtml, $newHtml));
	}

	function test_process_save5() {
		content::staticExpects($this->any())
			->method('process_image')
			->will($this->returnValue('sitecake-content/432.jpg'));
		$oldHtml =
				'<img src="images/123.jpg" data="0:0:200:200:0:0:200:200" />';
		$newHtml = '<p>text</p>' .
				'<img src="images/123.jpg" data="0:0:200:200:0:0:100:100" />';
		$this->assertEquals('<p>text</p><img src="sitecake-content/432.jpg" ' .
					'data="0:0:200:200:0:0:100:100" />', 
		content::process_save($oldHtml, $newHtml));
	}
	
	function test_image_info() {
		$GLOBALS['SC_ROOT'] = 'sc-test';
		$this->assertEquals(array(
				'id' => '1234',
				'ext' => 'jpg',
				'path' => 'sc-test' . DS . 'sitecake-content/1234.jpg',
				'name' => '1234.jpg'
			), 
			content::image_info('sitecake-content/1234.jpg'));
		$this->assertEquals(array(
				'id' => '123',
				'ext' => 'jpg',
				'path' => 'sc-test' . DS . 'images/test/ui/123.4.jpg',
				'name' => '123.4.jpg'
			), 
			content::image_info('images/test/ui/123.4.jpg'));
	}	
}