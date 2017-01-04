<?php
	set_include_path('../../');

	require_once 'php/dao/_util.php';
	require_once 'php/data/rec/sql/_SqlRec.php';
	//require_once 'php/data/LoginSession.php';
	//require_once "php/data/rec/sql/Auditing.php";
	require_once 'php/dao/FacesheetDao.php';
	$facesheet = null;
	
	
	//This script can be seen in action in face.php.
	//The difference between this test and getsCorrectPatientDataSections is that this test will load ONLY the data necessarry to display the facesheet
	//properly - this test is aiming to preserve the state of facesheet.php.
	
	//getsCorrectPatientDataSections is a more fine-grained test that tests each individual section by itself to make sure they load.
	try {
		LoginSession::verify_forServer()->requires($login->Role->Patient->facesheet);
		$facesheet = FacesheetDao::getFacesheet(16665); //ShXXX XXXLE
	}
	catch (Exception $e) {
		echo 'Got ERROR: <pre>' . $e->getMessage() . '</pre>';
	}
	
	echo 'facesheet is a ' . gettype($facesheet);
	echo 'Contents: <pre>' . print_r($facesheet, true) . '</pre>';
	
	$testPassed = false;
	
	if (gettype($facesheet) == 'string') {
		$testPassed = true;
	}
	
	include('postTestProcedures.php');
?>