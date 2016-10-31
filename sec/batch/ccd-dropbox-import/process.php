<?php
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');
	
	$folderName = 'uploads/';
	
	set_include_path('../../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	require_once 'batch/_batch.php'; //Simply include this in order to use Logger::debug()
	require_once 'php/data/rec/sql/dao/Logger.php'; //analytics/sec/logs/log.txt
	
	
	
	Logger::debug('PROCESS: ------------------');
	Logger::debug('PROCESS: Opening database....');
	//Queue the Oracle database for all records that need to be processed by looking at the 'STATUS' column.
	try {
		Logger::debug('PROCESS: Connecting to Oracle with DB user ' . MyEnv::$DB_USER . ', password ' . MyEnv::$DB_USER . ' and server ' . MyEnv::$DB_SERVER . ' and proc name ' . MyEnv::$DB_PROC_NAME);
		$conn = oci_connect(MyEnv::$DB_USER, MyEnv::$DB_PW, MyEnv::$DB_SERVER . '/' . MyEnv::$DB_PROC_NAME);
		
		if(!$conn) {
			$err = oci_error();
			throw new RuntimeException($err['message']);	
		}
	}
	catch (Exception $e) {
		Logger::debug('PROCESS: Error opening the database: ' . $e->getMessage());
		Logger::debug('PROCESS: ------------------');
		exit;
	}
	//bool $validLogin = authenticate_user1($uid, $password);
	
	
	
	Logger::debug('PROCESS: Connection successful and logged in! Looping through rows....');
	$stid = oci_parse($conn, "select upload.upload_id, upload.user_group_id, upload.name, upload.blob_content, upload.status, 
			user_groups.upload_uid, user_groups.upload_pw, user_groups.user_group_id
			from upload
			left join user_groups
			on upload.user_group_id = user_groups.USER_GROUP_ID
			where upload.status = 'UPLOAD REQUESTED'
			order by upload.user_group_id");
	oci_execute($stid);
	
	set_time_limit(10800); //3 hours. We could end up having to process hundreds of xml files.
	while (($rowEntry = oci_fetch_assoc($stid)) != false) {
		
		//For each record that we found, look at the BLOB content and make a file out of it.
		Logger::debug('PROCESS: Got a row: ' .  $rowEntry['UPLOAD_ID'] . ' | practice ID ' . $rowEntry['USER_GROUP_ID'] . ', filename is ' . $rowEntry['NAME']);
		try {
			//Make sure file is there. Create it if it doesn't exist.
			$filename = $folderName . $rowEntry['NAME'];
			Logger::debug('PROCESS: Determined filename to be ' . $filename);
			$handle = fopen($filename, 'a');
			
			if ($handle === false) {
				$err = error_get_last();
				throw new RuntimeException('Could not create the file ' . $filename . ': ' . $err['message']);
			}
			fclose($handle);
			
			Logger::debug('PROCESS: File opened successfully. Exporting blob into file....');
			// OCI-Lob object
			$ociLob = $rowEntry['BLOB_CONTENT'];
			
			if (!$ociLob->export($filename)) {
                $err = error_get_last();
				throw new RuntimeException('Could not write the BLOB content to the file ' . $filename . ': ' . $err['message']);
            }
			Logger::debug('PROCESS: Blob written to file successfully!');
			
			//Logger::debug('PROCESS: Wrote to the file!';
			
			//Now use our CCD API to do work on the file!
			//First login
			Logger::debug('PROCESS: Logging in as ' . $rowEntry['UPLOAD_UID'] . ' on ' . MyEnv::$CCD_INLOAD_HOST_IP . '...');
			
			//Logger::debug('PROCESS: <br>STR: operation=login&userId=' . $rowEntry['UPLOAD_UID'] . '&password=' . $rowEntry['UPLOAD_PW'] . '&practiceId=' . $rowEntry['USER_GROUP_ID'] . '---------';
			//continue;
			
			try {
				
				$handle = curl_init();
				$postStr = 'operation=login&userId=' . $rowEntry['UPLOAD_UID'] . '&password=' . $rowEntry['UPLOAD_PW'] . '&practiceId=' . $rowEntry['USER_GROUP_ID'];//&filename=' . $rowEntry['NAME'] . '&filepath=' . $folderName;
				Logger::debug('PROCESS: process.php: Using post string ' . $postStr);

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
				Logger::debug('PROCESS: cURL successful! Closing handle....');
				curl_close($handle);
				
				Logger::debug('PROCESS: Login: Result is `' . gettype($result) . ' ' . $result . '`');
			}
			catch (Exception $e) {
				Logger::debug('PROCESS: Got an exception: ' . $e->getMessage());// . ' ' . $e->getTraceAsString();
				exit;
			}
			Logger::debug('PROCESS: process.php: Finished cURL operation and got a result.');
			$resultStr = substr($result, 0, 2);
			
			if ($resultStr !== 'OK') {
				Logger::debug('PROCESS: ERROR: Could not log in with that result! cannot continue! Got result ' . gettype($result) . ' ' . $result);
				var_dump(debug_print_backtrace());
				//continue;
				exit;
			}
			$sessionId = substr($result, 7);
			Logger::debug('PROCESS: Login successful! Initializing and executing cURL. session ID is ' . $sessionId);
			
			$handle = curl_init();
			$postStr = 'operation=ccdupload&filename=' . $rowEntry['NAME'] . '&filepath=' . $folderName . '&sessionId=' . $sessionId;
			//$postStr = 'operation=ccdupload&filename=sample_cda.xml&filepath=uploads/&sessionId=' . $sessionId; //used to be kba6m0i3pqdbs56bfkp8uahhr2';
			Logger::debug('PROCESS: Calling ' . MyEnv::$BASE_URL . '/api/cerberus.php with post string ' . $postStr);

			curl_setopt($handle, CURLOPT_URL, 'http://127.0.0.1:80/analytics/api/cerberus.php');
			curl_setopt($handle, CURLOPT_HEADER, 0);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1); //Keep curl_exec quiet by stopping it from echoing output
			curl_setopt($handle, CURLOPT_POST, 4); 
			curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query(array('operation' => 'ccdupload',
																			'filename' => $rowEntry['NAME'], //was sample_cda.xml before.
																			'filepath' => 'uploads/',
																			'sessionId' => $sessionId, //used to be 'kba6m0i3pqdbs56bfkp8uahhr2',
																			'userGroupId' => $rowEntry['USER_GROUP_ID'], //should be 706
																			'IS_BATCH' => '1')
																		)
			);
			
			$result = curl_exec($handle);
			
			if (!$result) {
				$curlErrNo = curl_errno($handle);
				$curlErrMsg = curl_error($handle);
				curl_close($handle);
				
				$err = error_get_last();
				if ($curlErrNo > 0) {
					echo 'Filename ' . $rowEntry['NAME'] . ': CURL error #' . $curlErrNo . ': ' . $curlErrMsg . '. See the log file in /logs/log.txt and /api/blog.txt for more details! Continuing...';
				}
				//
				//throw new RuntimeException('CURL error ' . $curlErrNo . ': ' . $curlErrMsg . ' [PHP said ' . $err['message'] . ']');
			}
			//curl_close($handle);
			Logger::debug('PROCESS: Our cURL result is ' . gettype($result) . ' ' . $result);
			Logger::debug('PROCESS: ------------------');
			continue;
		}
		catch (Exception $e) {
			Logger::debug($e->getMessage());
			Logger::debug('PROCESS: ------------------');
			exit;
		}
		exit;
	}
	
	//Cleanup
	
	oci_free_statement($stid);
	oci_close($conn);
?>