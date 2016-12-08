<?php
	$testPassed = false;

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
		$testPassed = true;
		curl_close($handle);
	}
	catch (Exception $e) {
		echo 'ERROR: ' . $e->getMessage();
	}
	
	include('postTestProcedures.php');
?>