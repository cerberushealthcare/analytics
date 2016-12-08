<?php
	//This test will assume the BLOB was ALREADY CREATED - it does not look at the database and get BLOB content.
	//If something goes wrong, api/cerberus.php will update the upload row's PROCESS_MSG with debug info. The reason we can't do it in this file is because cURL does not get
	//any echo'ed output for whatever reason.
	
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');
	
	$folderName = 'uploads/';
	$filename = '2645_38267_ELLIOTT_DAN_20161113142256.xml';
	$testUploadId = 279;
	$testPassed = false;

	set_include_path('../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	//require_once 'batch/_batch.php'; //Simply include this in order to use blog()
	require_once 'php/data/rec/sql/dao/Dao.php'; //analytics/sec/logs/log.txt
	
	
	
	//echo 'Opening database....' . '<br>';
	//Queue the Oracle database for all records that need to be processed by looking at the 'STATUS' column.
	/*try {
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
	}*/
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
	
	try {
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
																		'upload_id' => $testUploadId,
																		'sessionId' => $sessionId,
																		'userGroupId' => 705, //This will go directly into CLIENTS.USER_GROUP_ID. It used to go into CLIENTS.UID but that was changed.
																		'IS_BATCH' => '1')
																	)
		);
		
		$result = curl_exec($handle);
		
		echo 'The result is ' . gettype($result) . ' ' . sizeof($result) . '<br>';
		
		if (strlen($result) > 0) {
			try {
				setUploadTableStatus($rowEntry['UPLOAD_ID'], 'FAILED');
				appendToErrorLogColumn($rowEntry['UPLOAD_ID'], 'ccd_file_upload test: Error during import: ' . $e->getMessage());
			}
			catch (Exception $e) {
				blog('Something went wrong with updating the status column: ' . $e->getMessage());
			}
		}
		else {
			try {
				setUploadTableStatus($testUploadId, 'UPLOADED');
			}
			catch (Exception $e) {
				blog('Something went wrong with updating the status column: ' . $e->getMessage());
			}
			$testPassed = true;
		}
		
		
		
		/*if (!$result) {
			$curlErrNo = curl_errno($handle);
			$curlErrMsg = curl_error($handle);
			curl_close($handle);
			
			$err = error_get_last();
			echo 'CURL error ' . $curlErrNo . ': ' . $curlErrMsg . ' [PHP said ' . $err['message'] . ']<br>';
			setUploadTableStatus($testUploadId, 'FAILED');
			echo 'Something went wrong. We are appending the string ' . $curlErrMsg . '<br>';
			appendToErrorLogColumn($testUploadId, 'ccd_file_upload test: An error occured: ' . $curlErrMsg);
			//throw new RuntimeException('CURL error ' . $curlErrNo . ': ' . $curlErrMsg . ' [PHP said ' . $err['message'] . ']');
		}
		else {
			curl_close($handle);
			echo '<pre>';
			echo 'TESTFILE: Our cURL result is ' . gettype($result) . ' ' . $result;
			echo '</pre>';
			
			if ($result == '' && gettype($result) == 'string') {
				$testPassed = true;
			}
			else {
				throw new RuntimeException($result);
			}
		}*/
		
	}
	catch (Exception $e) {
		echo 'Test error: ' . $e->getMessage();
		appendToErrorLogColumn($testUploadId, $e->getMessage());
	}
		
	function setUploadTableStatus($rowID, $str) {
		$sql = "update UPLOAD set STATUS = '" . $str . "' where UPLOAD_ID = " . $rowID;
		$res = Dao::query($sql);
		
		if (oci_error($res)) {
			$err = oci_error($res);
			echo 'Error: Could not update the UPLOAD table record ' . $rowID . ' to ' . $str . ': ' . $err['message'];
		}
		else {
			echo 'UPLOAD table record ' . $rowID . ' updated to ' . $str . ' successfully!';
		}
	}
	
	function appendToErrorLogColumn($rowID, $str) {
		
		$sql = "DECLARE
					longText CLOB;
				BEGIN
					longText := '" . $str . "';
				
					update upload
					set PROCESS_MSG = PROCESS_MSG || longText || chr(10)
					where UPLOAD_ID = " . $rowID . ";
				END;";
	  
	    echo 'appendToErrorLog: Doing query ' . $sql . '<br><br>';
		try {
			$res = Dao::query($sql);
				
			if (oci_error($res)) {
				$err = oci_error($res);
				echo 'Error: Could not update the UPLOAD table record ' . $rowID . ' to ' . $str . ': ' . $err['message'];
			}
			else {
				echo 'UPLOAD table PROCESS_MSG column for ID ' . $rowID . ' updated to ' . $str . ' successfully!';
			}
		}
		catch (Exception $e) {
			echo 'ERROR: Could not update the UPLOADS table error column: ' . $e->getMessage();
		}
    }
	
	
	
	
	
	//Cleanup
	//oci_free_statement($stid);
	//oci_close($conn);
	
	echo 'The result we got is ' . gettype($result) . ' ' . print_r($result) . '. the string is ' . $result;
	echo '<br>If this tests fails, check out /api/blog.txt as well as logs/log.txt!<br>';
	
	include('postTestProcedures.php');
?>