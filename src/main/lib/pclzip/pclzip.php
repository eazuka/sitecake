<?php
namespace pclzip;

require_once('pclzip.lib.php');

class pclzip {
	static function extract($zipfile, $dest) {
		$z = new \PclZip($zipfile);
		$res = $z->extract($dest);
		return is_array($res) ? true : false;
	}
}