<?php
namespace sitecake;

use Zend\Http\PhpEnvironment\Response;
use Zend\Http\PhpEnvironment\Request;

class http {
	static $req;
	
	static function __static_init() {
		static::$req = http::request();
	}
	
	static function request() {
		if (!http::$req) {
			http::$req = new Request();				
		}
		return http::$req;
	}
	
	static function response($body) {
		$res = new Response();
		return $res->setStatusCode(Response::STATUS_CODE_200)
			->setContent((string)$body);
	}
	
	static function notFoundResponse($uri) {
		$res = new Response();
		return $res->setStatusCode(Response::STATUS_CODE_404)
			->setContent(
				'<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
				<html><head><title>404 Not Found</title></head>
				<body><h1>Not Found</h1><p>The requested page ' . $uri . 
				' was not found on this server.</p></body></html>');
	}

	static function errorResponse($error) {
		$res = new Response();
		return $res->setStatusCode(Response::STATUS_CODE_500)
			->setContent(
				'<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
				<html><head><title>500 Internal Server Error</title></head>
				<body><h1>Internal Server Error</h1>' . $error . 
				'</body></html>');
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param Response $response
	 */
	static function send($response) {
		$response->send();
	}	
}
