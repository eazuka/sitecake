<?php
namespace sitecake;

class rendererTest extends \PHPUnit_Framework_TestCaseExt {

	static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\renderer');
	}

	function test_extract_refs() {
		$text = '<body>something 3343.3434 ' .
			'src="path/1234567890123456789012345678901234567890.jpg"> ' .
			'href="someghint0234567890123456789012345678901234567890" ' .
			'src="path/0123456789012345678901234567890123456789.gif" ' .
			'data="//abcdef1234567890123456789012345678901234.doc"';
		$this->assertEquals(array(
			'1234567890123456789012345678901234567890',
			'0123456789012345678901234567890123456789',
			'abcdef1234567890123456789012345678901234'),
			renderer::extract_refs($text));
	}
}