<?php
namespace sitecake;

use \Exception as Exception;
use Zend\Json\Json as Json;
use Zend\Http\Request as Request;

class service {
	
	static function login(Request $req) {
		$params = $req->query();
		return service::response($params, 
			session::login($params['credential']));
	}
	
	static function logout(Request $req) {
		return service::response($req->query(), 
			session::logout());
	}
	
	static function change(Request $req) {
		$params = $req->query();
		return service::response($params, 
			session::change($params['credential'], $params['newCredential']));
	}

	static function alive(Request $req) {
		return service::response($req->query(),
			session::alive());
	}
	
	static function upload(Request $req) {
		return service::response($req->query(), upload::upload($req));	
	}
	
	static function save(Request $req) {
		return service::response($req->query(), content::save($req->post()));	
	}
	
	static function publish(Request $req) {
		return service::response($req->query(), content::publish($req->post()));
	}
		
	private static function response($params, $data)
	{
		return http::response(isset($params['callback']) ? 
				$params['callback'] . '(' . Json::encode($data) . ')' :
				Json::encode($data));
	}	
}