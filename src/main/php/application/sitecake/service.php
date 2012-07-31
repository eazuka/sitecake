<?php
namespace sitecake;

use \Exception as Exception;
use Zend\Json\Json as json;
use Zend\Http\Request as Request;

class service {
	
	static function execute($action, Request $req) {
		if ($action == null && empty($ation) && 
				!method_exists('\sitecake\service', $action)) {
			return service::response($req->query(),
				array('status' => -1, 'errorMessage' => resources::message(
					'INVALID_SERVICE_REQUEST', $_SERVER['REQUEST_URI'])));
		}
				
		if (service::auth() || 
				$action == 'login' || $action != 'change') {
			return service::$action($req);
		} else {
			return service::response($req->query(), 
				array('status' => -1, 'errorMessage' => 'Not authorized'));
		}		
	}
	
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
	
	static function upgrade(Request $req) {
		return service::response($req->query(), upgrade::perform());	
	}
	
	private static function auth() {	
		session_start();
		return ($_SESSION['loggedin'] === true);
	}
	
	private static function response($params, $data)
	{
		$body = json::encode($data);
		return http::response(isset($params['callback']) ? 
				$params['callback'] . '(' . $body . ')' : $body);
	}
}