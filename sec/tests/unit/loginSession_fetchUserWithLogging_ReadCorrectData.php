<?php
	set_include_path('../../../');
	
	require_once 'config/MyEnv.php';
	require_once 'php/data/rec/sql/UserLogins.php';
	require_once('php/data/LoginSession.php');
	
	
	$testPassed = false;
	
	$user = LoginSession::testFetchUser('mm', 'clicktate1', true); //simply calls fetchUser_withLogging() - testFetchUser is public whereas fetchUser_withLogging is private and cannot directly be called.
	echo 'Got user ' . gettype($user) . ' <pre>' . print_r($user, true) . '</pre>';
	echo '<br> User name is ' . $user->name;
	if ($user->name == 'mm' && $user->pw == 'FB78B84E04DCC80263D727F9BA8F8ABF') $testPassed = true;
	
	include('../postTestProcedures.php');
?>