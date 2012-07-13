<?php
include('bootstrap.php');

$errors = \sitecake\env::ensure();
if (empty($errors)) {
	sitecake\renderer::process();
} else {
	sitecake\http::send(
		sitecake\http::errorResponse(implode('<br/>', $errors)));
}

