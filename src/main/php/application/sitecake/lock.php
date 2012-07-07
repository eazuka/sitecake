<?php
namespace sitecake;

class lock {
	static function create($name, $timeout = 0) {
		io::file_put_contents(lock::lock($name), $timeout);
	}
	
	static function reset($name) {
		io::touch(lock::lock($name));
	}
	
	static function remove($name) {
		io::unlink(lock::lock($name));
	}
	
	static function exists($name) {
		$file = lock::lock($name);
		if (io::file_exists($file)) {
			if (lock::isTimedOut($file)) {
				lock::remove($file);
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	static function isTimedOut($lock) {
		$timeout = io::file_get_contents($lock);
		return $timeout == 0 ? 
			false : (io::filemtime($lock) + $timeout) > time();
	}
	
	static function lock($name) {
		return $GLOBALS['TEMP'] . DS . $name . '.lock';
	}
}