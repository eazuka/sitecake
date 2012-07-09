<?php
namespace sitecake;

use Zend\Json\Json as json;

class draft {
	static function exists($id) {
		return io::file_exists(draft::path($id));
	}
	
	static function get($id) {
		if (draft::exists($id)) {
			return json::decode(io::file_get_contents(draft::path($id)), 
				json::TYPE_ARRAY);
		} else {
			return array();
		}
	}
	
	static function update($id, $data) {
		io::file_put_contents(draft::path($id), json::encode($data));
	}
	
	static function delete($id) {
		if (io::file_exists(draft::path($id))) {
			io::unlink(draft::path($id));
		}
	}
	
	static function path($id) {
		return $GLOBALS['DRAFT_CONTENT_DIR'] . DS . $id . '.drf';
	}
}