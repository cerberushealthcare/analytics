<?php
	
	set_include_path('../');
	ob_start('ob_gzhandler');
	require_once 'server.php';
	require_once 'php/c/patient-list/PatientList.php';
	require_once 'php/c/patient-entry/PatientEntry.php';
	
	$testPassed = false;
	$limit = 100; //Keep this at least 1 less than the amount of expected rows.
	$lastName = 'johnson'; //Should automatically become uppercase
	$firstName = '';
	$uid = '';
	$birth = '';

	LoginSession::verify_forServer();
	//You can see this test in action in sec/serverPatients.php.
	$recs = PatientList::search($lastName, $firstName, $uid, $birth, 1, $limit);
	
	echo 'Got ' . sizeof($recs) . ' rows. recs is a ' . gettype($recs) . '<br>';
	echo '<pre>Second array element: ' . print_r($recs[1], true) . '</pre>'; //should be a PStub_Search object.
	
	foreach ($recs as &$row) {
		echo $row->lastName . ', ' . $row->firstName . '<br>';
	}
	
	if (sizeof($recs) == $limit && gettype($recs[1]) == 'object') $testPassed = true;
	
	include('postTestProcedures.php');
?>

