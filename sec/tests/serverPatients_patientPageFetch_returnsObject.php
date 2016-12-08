<?php
	set_include_path('../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	require_once 'serverPatients.php';
	$user = null;
	
	try {
		//fetch(userGroupId, limit, page, activeOnly, byMru)
		$page = PatientPage::fetch(705, 35, 1, 0, 0);
	}
	catch (Exception $e) {
		echo 'Got ERROR: <pre>' . $e->getMessage() . '</pre>';
	}
	
	echo 'page is a ' . gettype($page) . ' <pre>' . var_dump($page) . '</pre>';
	
	$testPassed = false;
	
	$decode = json_decode($page);
	
	if (gettype($page) == 'object') {
		$testPassed = true;
	}
	
	include('postTestProcedures.php');
?>