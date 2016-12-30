<?php
	set_include_path('../../');

	require_once 'php/dao/_util.php';
	require_once 'php/data/rec/sql/_SqlRec.php';
	require_once 'php/dao/FacesheetDao.php';
	$facesheet = null;
	$testPassed = false;
	
	//This script can be seen in action in face.php.
	//To make this test pass, make sure that the client ID you specify for getMedClientHistory has at least one medication.
	try {
		LoginSession::verify_forServer()->requires($login->Role->Patient->facesheet);
		$facesheet = FacesheetDao::testFacesheet(16665, 5);
	}
	catch (Exception $e) {
		echo 'Got ERROR: <pre>' . $e->getMessage() . ' (try logging in?)</pre>';
	}
	
	echo 'facesheet is a ' . gettype($facesheet);
	echo 'Contents: <pre>' . print_r($facesheet, true) . '</pre>';
	
	if (gettype($facesheet) == 'object' && property_exists($facesheet, 'vitals')) {
		$testPassed = true;
	}
	
	include('../postTestProcedures.php');
?>