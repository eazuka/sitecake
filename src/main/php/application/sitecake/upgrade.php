<?php
namespace sitecake;

use Zend\Http\ClientStatic as client;
use Zend\Http\Response as Response;
use \ZipArchive as ZipArchive;
use pclzip\pclzip as pclzip;

class upgrade {
	static function perform() {
		$latest = upgrade::latest_remote();
		$current = upgrade::latest_local();
		return ($latest > $current) ?
			upgrade::upgrade_to(upgrade::to_version($latest)) :
			array('status' => 0, 'upgrade' => 0);
	}
	
	static function latest_remote() {
		$resp = client::get('http://sitecake.com/dl/upgrade/latest.txt');
		if ($resp->isSuccess()) {
			return upgrade::version($resp->getBody());
		} else {
			return -1;
		}
	}
	
	static function latest_local() {
		$versions = io::glob($GLOBALS['SC_ROOT'] . DS . 'sitecake' . DS . 
			'*.*.*', GLOB_ONLYDIR);
		return array_reduce($versions, function($latest, $item) {
			$curr = upgrade::version($item);
			return ($curr > $latest) ? $curr : $latest; 
		}, -1);
	}
	
	static function upgrade_to($ver) {
		$file = upgrade::download($ver);
		if (is_array($file)) {
			$res = $file;
		} else {
			$res = upgrade::extract($ver, $file);
			io::unlink($file);
		}
		if ($res['status'] == 0) {
			upgrade::switch_to($ver);
		}
		return $res;
	}
	
	static function download($ver) {
		$url = 'http://sitecake.com/dl/upgrade/sitecake-' . 
			$ver . '-upgrade.zip';
		$resp = client::get($url);
		if ($resp->isSuccess()) {
			$file = $GLOBALS['TEMP'] . DS . 'sitecake-' . $ver . '-upgrade.zip';
			io::file_put_contents($file, $resp->getBody());
			return $file;
		} else {
			return array('status' => -1, 
				'errorMessage' => 'Unable to download upgrade from ' . $url);
		}		
	}
	
	static function extract($ver, $file) {
		$dir = $GLOBALS['SC_ROOT'] . DS . 'sitecake';
		if (class_exists('ZipArchive')) {
			$res = upgrade::extract_ziparchive($file, $dir);
		} else {
			$res = pclzip::extract($file, $dir);
		}
		return $res ? 
			array('status' => 0, 'upgrade' => 1, 'latest' => $ver) :
			array('status' => -1, 
				'errorMessage' => 'Unable to extract the upgrade archive');
	}
	
	static function switch_to($ver) {
		io::file_put_contents(
			$GLOBALS['SC_ROOT'] . DS . 'sc-admin.php',
			"<?php include 'sitecake/$ver/server/admin.php';");	
	}
	
	static function extract_ziparchive($zipfile, $dest) {
		$z = new ZipArchive();
		if ($z->open($zipfile) === true) {
			return $z->extractTo($dest);
		} else {
			return false;
		}
	}
	
	static function version($str) {
		if (preg_match('/([0-9]+)\.([0-9]+)\.([0-9]+)/', trim($str), $matches) > 
				0) {
			return $matches[1]*1000000 + $matches[2]*1000 + $matches[3];
		} else {
			return -1;
		}
	}
	
	static function to_version($num) {
		$major = floor($num/1000000);
		$minor = floor(($num - $major*1000000)/1000);
		$rev = $num - $major*1000000 - $minor*1000;
		return "$major.$minor.$rev";
	}
}