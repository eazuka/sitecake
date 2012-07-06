<?php
include('bootstrap.php');

use sitecake\http,
	sitecake\resources;

$action = isset($_GET['action']) ? $_GET['action'] : false;

if ($action && method_exists('\sitecake\service', $action)) {
	try {
		http::send(call_user_func(
			array('\sitecake\service', $action), http::request()));
	} catch (Exception $e) {
		http::send(http::errorResponse('<h2>Exception: </h2><b>' .
			$e->getMessage() . "</b><br/>" .
			$e->getFile() . '(' . $e->getLine() . '): <br/>' .
			implode("<br/>", explode("\n", $e->getTraceAsString()))));		
	}
} else {
	http::send(http::errorResponse(resources::message(
		'INVALID_SERVICE_REQUEST', $_SERVER['REQUEST_URI'])));
}