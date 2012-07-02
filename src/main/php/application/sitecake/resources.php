<?php
namespace sitecake;

class resources {
	static $map;
	
	/**
	 * Called by the class loader.
	 */
	static function __static_init() {
		static::loadMap();	
	}
	
	/**
	 * Initializes the message map from messages.php.
	 */
	static function loadMap() {
		include('messages.php');
		static::$map = $messagesMap;
	}
	
	/**
	 * Returns a text message that corresponds to the given key or
	 * the given default value if the key is not found.
	 *
	 * @param string $key the message key
	 * @param mixin $params an array with values or a single value for replacing
	 * 						place holders {n} in the message text
	 * @param string $default the default return value if the key is not found
	 * @return string
	 */
	static function message($key, $params = null, $default = null) {
		$value = isset(static::$map[$key]) ? static::$map[$key] : 
			($default ? $default : $key);
		if ($params != null && !is_array($params))
			$params = array($params);
		$value = preg_replace_callback('({([0-9]+)})', function($match) use ($params) {
			if (is_array($params) && isset($params[$match[1]]))
				return $params[$match[1]];
			else
				return $match[0];
		}, $value);
		return $value;
	}
}