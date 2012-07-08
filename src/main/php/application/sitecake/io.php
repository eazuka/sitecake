<?php
namespace sitecake;

class io {
	static function file_get_contents($filename, $use_include_path = false,
			$context = null, $offset = -1) {
		return file_get_contents(
			$filename, $use_include_path, $context, $offset);
	}

	static function file_put_contents($filename, $data, $flags = 0, 
			$context = null) {
		return file_put_contents($filename, $data, $flags, $context);
	}
	
	static function file_exists($filename) {
		return file_exists($filename);
	}
	
	static function unlink($filename, $context = null) {
		return $context ? unlink($filename, $context) : unlink($filename);
	}
	
	static function touch($filename, $time = -1, $context = null) {
		if ($time < 0) {
			$time = time();
		}
		return $context ? touch($filename, $time, $context) :
			touch($filename, $time);
	
	}
	
	static function copy($source, $dest, $context = null) {
		return $context ? copy($source, $dest, $context) : copy($source, $dest);	
	}
	
	static function filemtime($filename) {
		return filemtime($filename);
	}
	
	static function mkdir($pathname, $mode = null, $recursive = null, 
			$context = null) {
		return $context ? mkdir($pathname, $mode, $recursive, $context) :
			mkdir($pathname, $mode, $recursive);
	}
	
	static function is_writable($filename) {
		return is_writable($filename);
	}
	
	static function is_readable($filename) {
		return is_readable($filename);
	}
	
	static function glob($pattern, $flags = 0) {
		return glob($pattern, $flags);
	}
	
	static function fopen($filename, $mode, $use_include_path = false, 
			$context = null) {
		return $context ? fopen($filename, $mode, $use_include_path, $context) :
			fopen($filename, $mode, $use_include_path);
	}
	
	static function fclose($handle) {
		return fclose($handle);
	}
	
	static function fread($handle, $length = null) {
		return fread($handle, $length);
	}
	
	static function fwrite($handle, $string, $length = null) {
		return fwrite($handle, $string, $length);
	}
}