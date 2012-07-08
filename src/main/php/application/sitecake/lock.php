<?php
namespace sitecake;

class lock {
	static function create($name, $timeout = 0) {
		io::file_put_contents(lock::path($name), $timeout);
	}
	
	static function reset($name) {
		$path = lock::path($name);
		if (io::file_exists($path)) {
			io::touch(lock::path($name));
		}		
	}
	
	static function remove($name) {
		$path = lock::path($name);
		if (io::file_exists($path)) {
			io::unlink($path);
		}
	}
	
	static function exists($name) {
		$file = lock::path($name);
		if (io::file_exists($file)) {
			if (lock::timedout($file)) {
				lock::remove($file);
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	static function timedout($lock) {
		$timeout = io::file_get_contents($lock);
		return $timeout == 0 ? 
			false : (io::filemtime($lock) + $timeout) > time();
	}
	
	static function path($name) {
		return $GLOBALS['TEMP'] . DS . $name . '.lock';
	}
}