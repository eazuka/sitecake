<?php
namespace sitecake;

//require_once('phpQuery-onefile.php');
use \phpQuery\phpQuery as phpQuery;

class core {
	
	static function doSomething($param1, $param2) {
//echo "core::doSomething\n";
		phpQuery::newDocumentXML('<html><head></head><body></body></html>');
		//phpQuery::pq('<div />');
		return core::someOtherMethod($param2) . static::someMethod($param1);
	}
	
	static function someMethod($input) {
//echo "core::someMethod\n";
		return $input;
	}
	
	static function someOtherMethod($input) {
//echo "core::someOtherMethod\n";
//		return $input;
		return util::someUtilFunc($input);
	}
}
