<?php
namespace sitecake;

class env {
	static function ensure() {
		return array_merge(array(), 
			env::ensureDirectory($GLOBALS['SC_ROOT'], false, true),
			env::ensureDirectory($GLOBALS['DRAFT_CONTENT_DIR']),
			env::ensureDirectory($GLOBALS['PUBLIC_IMAGES_DIR']),
			env::ensureDirectory($GLOBALS['PUBLIC_FILES_DIR']),
			env::ensureDirectory($GLOBALS['TEMP']));
	}
	
	/**
	 * Check and/or create the given directory path.
	 * 
	 * @param string $path the required directory path
	 * @param boolean $create create the directory if not exists
	 * @param boolean $writable check if the directory is writable
	 * @return array with error text messages
	 */
	static function ensureDirectory($path, $create = true, $writable = true) {
		$errors = array();
		
		if (!io::file_exists($path)) {
			if (!$create) {
				array_push($errors,
					resources::message('DIR_NOT_EXISTS', $path));
			} elseif (!io::mkdir($path, 0775, true)) {
				array_push($errors,
					resources::message('DIR_NOT_CREATED', $path));
			}
		}
		
		if ($writable && !io::is_writable($path)) {
			array_push($errors, 
				resources::message('DIR_NOT_WRITABLE', $path));
		}

		return $errors;
	}
}