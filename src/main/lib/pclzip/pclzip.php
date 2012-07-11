<?php
namespace pclzip;

require_once('pclzip.lib.php');

class pclzip {
	static function open($path) {
		return new PclZip($path);		
	}
}