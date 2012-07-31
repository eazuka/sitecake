<?php
include('bootstrap.php');
include($GLOBALS['CREDENTIALS_FILE']);

use sitecake\http,
	sitecake\service;

$errors = \sitecake\env::ensure();
if (!empty($errors)) {
	sitecake\http::send(
		sitecake\http::errorResponse(implode('<br/>', $errors)));
}

$action = isset($_GET['action']) ? $_GET['action'] : false;
try {
	http::send(service::execute($action, http::request()));
} catch (Exception $e) {
	http::send(http::errorResponse('<h2>Exception: </h2><b>' .
		$e->getMessage() . "</b><br/>" .
		$e->getFile() . '(' . $e->getLine() . '): <br/>' .
		implode("<br/>", explode("\n", $e->getTraceAsString()))));		
}