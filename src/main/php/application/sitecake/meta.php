<?php
namespace sitecake;

use Zend\Json\Json as json;

class meta {
	/**
	 * Checks if there is a meta file for the specified object id.
	 * 
	 * @param string $id object id
	 * @return true if the meta file exists, false if not
	 */
	static function exists($id) {
		return io::file_exists(meta::path($id));
	}
	
	/**
	 * Returns the meta data for the specified object id.
	 * 
	 * @param string $id object id
	 * @param string $prop (optional) property name
	 * @return array with all meta data or a single property value if the prop
	 * 			name is specified
	 */
	static function get($id, $prop = null) {
		$data = json::decode(io::file_get_contents(meta::path($id)), 
			json::TYPE_ARRAY);
		return $prop ? $data[$prop] : $data;
	}
	
	/**
	 * Saves the given meta data for the specified object id. Any existing data
	 * will be replaced.
	 * 
	 * @param string $id object id
	 * @param array $data the object meta data
	 */
	static function put($id, $data) {
		return io::file_put_contents(meta::path($id), json::encode($data));
	}
	
	/**
	 * Updates the object meta data with the new properties. The existing and
	 * the given arrays will be merged (using PHP array_merge function).
	 * 
	 * @param string $id object id
	 * @param array $data the new meta data properties
	 */
	static function update($id, $data) {
		return meta::put($id, json::encode(array_merge(meta::get($id), $data)));	
	}
	
	/**
	 * Removes any existing object meta data.
	 * 
	 * @param string $id the object id
	 */
	static function remove($id) {
		return io::unlink(meta::path($id));
	}
	
	static function path($id) {
		return $GLOBALS['DRAFT_CONTENT_DIR'] . DS . $id . '.meta';
	}
}