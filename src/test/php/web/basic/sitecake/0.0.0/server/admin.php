<?php

$phpVersion = preg_split("/[:.]/", phpversion());
if ( ($phpVersion[0]*10 + $phpVersion[1]) < 53 ) {
	die("PHP version $phpVersion[0].$phpVersion[1] is found on your webhosting.
		PHP version 5.3 (or greater) is required.");
}

include('admin-full.php');