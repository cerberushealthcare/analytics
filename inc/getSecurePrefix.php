<?php 
function getSecurePrefix() {
	$host = substr($_SERVER['HTTP_HOST'], 0, 5);
	if ($host == "local" || $host == "test." || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
		return "";
	} else {
		return "https://www.clicktate.com/sec/";
	}
}
?>