<?php
	set_include_path('../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	require_once 'php/data/LoginSession.php';
	$user = null;
	
	try {
		$user = LoginSession::login('mm', 'clicktate1'); //loginsession.php
	}
	catch (Exception $e) {
		echo 'Got ERROR: <pre>' . $e->getMessage() . '</pre>';
	}
	
	echo 'User is a ' . gettype($user) . ' <pre>' . var_dump($user) . '</pre>';
	
	$testPassed = false;
	
	if (gettype($user) == 'object') {
		$testPassed = true;
	}
	
	include('postTestProcedures.php');
?>