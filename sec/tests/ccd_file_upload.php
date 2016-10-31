<?php
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');
	
	$folderName = 'uploads/';
	$filename = 'Driver_3909_CCD.XML';

	set_include_path('../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	//require_once 'batch/_batch.php'; //Simply include this in order to use blog()
	//require_once 'php/data/rec/sql/dao/Logger.php'; //analytics/sec/logs/log.txt
	
	
	
	echo 'Opening database....' . '<br>';
	//Queue the Oracle database for all records that need to be processed by looking at the 'STATUS' column.
	try {
		echo 'Connecting to Oracle with DB user ' . MyEnv::$DB_USER . ', password ' . MyEnv::$DB_USER . ' and server ' . MyEnv::$DB_SERVER . ' and proc name ' . MyEnv::$DB_PROC_NAME . '<br>';
		$conn = oci_connect(MyEnv::$DB_USER, MyEnv::$DB_PW, MyEnv::$DB_SERVER . '/' . MyEnv::$DB_PROC_NAME);
		
		if(!$conn) {
			$err = oci_error();
			throw new RuntimeException($err['message']);	
		}
	}
	catch (Exception $e) {
		echo 'Error opening the database: ' . $e->getMessage();
		exit;
	}
	//bool $validLogin = authenticate_user1($uid, $password);
	
	echo 'Logging in as mm on ' . MyEnv::$CCD_INLOAD_HOST_IP . '...' . '<br>';
	
	//blog('<br>STR: operation=login&userId=' . $rowEntry['UPLOAD_UID'] . '&password=' . $rowEntry['UPLOAD_PW'] . '&practiceId=' . $rowEntry['USER_GROUP_ID'] . '---------';
	//continue;
	
	try {
		$handle = curl_init();
		$postStr = 'operation=login&userId=mm&password=clicktate1&practiceId=705';//&filename=' . $rowEntry['NAME'] . '&filepath=' . $folderName;
		echo 'Using post string ' . $postStr . '<br>';

		curl_setopt($handle, CURLOPT_URL, '127.0.0.1/analytics/api/cerberus.php');
		curl_setopt($handle, CURLOPT_HEADER, 0);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1); //Keep curl_exec quiet by stopping it from echoing output
		curl_setopt($handle, CURLOPT_POST, 4); 
		curl_setopt($handle, CURLOPT_POSTFIELDS, $postStr);
		
		$result = curl_exec($handle);
		
		if (!$result) {
			$curlErrNo = curl_errno($handle);
			$curlErrMsg = curl_error($handle);
			curl_close($handle);
			
			$err = error_get_last();
			throw new RuntimeException('CURL login error ' . $curlErrNo . ': ' . $curlErrMsg . ' [PHP said ' . $err['message'] . ']');
		}
		echo 'cURL successful! Closing handle....' . '<br>';
		curl_close($handle);
		
		echo 'Login: Result is `' . gettype($result) . ' ' . $result . '`' . '<br>';
	}
	catch (Exception $e) {
		echo 'Got an exception: ' . $e->getMessage();
		exit;
	}
	echo 'process.php: Finished cURL operation and got a result.' . '<br>';
	$resultStr = substr($result, 0, 2);
	
	if ($resultStr !== 'OK') {
		echo 'ERROR: Could not log in with that result! cannot continue! Got result <b>' . gettype($result) . ' ' . $result . '</b><br>';
		var_dump(debug_print_backtrace());
		//continue;
		exit;
	}
	$sessionId = substr($result, 7);
	echo 'Login successful! Initializing and executing cURL. session ID is ' . $sessionId . '<br>';
	
	$handle = curl_init();
	$postStr = 'operation=ccdupload&filename=' . $filename . '&filepath=' . $folderName . '&sessionId=' . $sessionId;
	//$postStr = 'operation=ccdupload&filename=sample_cda.xml&filepath=uploads/&sessionId=' . $sessionId; //used to be kba6m0i3pqdbs56bfkp8uahhr2';
	echo 'Calling ' . MyEnv::$BASE_URL . '/api/cerberus.php with post string ' . $postStr . '<br>';

	curl_setopt($handle, CURLOPT_URL, 'http://127.0.0.1:80/analytics/api/cerberus.php');
	curl_setopt($handle, CURLOPT_HEADER, 0);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1); //Keep curl_exec quiet by stopping it from echoing output
	curl_setopt($handle, CURLOPT_POST, 4); 
	curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query(array('operation' => 'ccdupload',
																	'filename' => $filename, //was sample_cda.xml before.
																	'filepath' => 'uploads/',
																	'sessionId' => $sessionId,
																	'userGroupId' => 712, //This will go directly into CLIENTS.USER_GROUP_ID. It used to go into CLIENTS.UID but that was changed.
																	'IS_BATCH' => '1')
																)
	);
	
	$result = curl_exec($handle);
	
	if (!$result) {
		$curlErrNo = curl_errno($handle);
		$curlErrMsg = curl_error($handle);
		curl_close($handle);
		
		$err = error_get_last();
		echo 'CURL error ' . $curlErrNo . ': ' . $curlErrMsg . ' [PHP said ' . $err['message'] . ']';
		//throw new RuntimeException('CURL error ' . $curlErrNo . ': ' . $curlErrMsg . ' [PHP said ' . $err['message'] . ']');
	}
	//curl_close($handle);
	echo '<pre>';
	echo 'Our cURL result is ' . gettype($result) . ' ' . $result;
	echo '</pre>';
	
	
	
	//Cleanup
	//oci_free_statement($stid);
	oci_close($conn);
	
	
	$testPassed = false;
	if (strlen($result) == 0) $testPassed = true;
	include('postTestProcedures.php');
?>