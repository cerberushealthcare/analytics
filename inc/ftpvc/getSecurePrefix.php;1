<?php 
function getSecurePrefix() {
	$host = substr($_SERVER['HTTP_HOST'], 0, 5);
	if ($host == "local" || $host == "test.") {
		return "sec/";
	} else {
		return "https://www.clicktate.com/sec/";
	}
}
?>