<?php
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');
	
	
	$folderName = 'C:/www/clicktate/cert/sec/analytics/uploads';
	
	set_include_path('../../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	require_once 'batch/_batch.php'; //Simply include this in order to use blog()
	require_once 'php/dao/_util.php'; //This has the openOracle() function in it that is needd for the Dao to work. We should probably put it in the Dao.php file at some point.
	require_once 'php/data/rec/sql/dao/Dao.php';
	//require_once 'php/data/rec/sql/dao/Logger.php'; //analytics/sec/logs/log.txt
	
	
	
	blog('------------------');
	blog('Opening database....');
	//Queue the Oracle database for all records that need to be processed by looking at the 'STATUS' column.
	try {
		blog('Connecting to Oracle with DB user ' . MyEnv::$DB_USER . ', password ' . MyEnv::$DB_USER . ' and server ' . MyEnv::$DB_SERVER . ' and proc name ' . MyEnv::$DB_PROC_NAME);
		$conn = oci_connect(MyEnv::$DB_USER, MyEnv::$DB_PW, MyEnv::$DB_SERVER . '/' . MyEnv::$DB_PROC_NAME);
		
		if(!$conn) {
			$err = oci_error();
			throw new RuntimeException($err['message']);	
		}
	}
	catch (Exception $e) {
		blog('Error opening the database: ' . $e->getMessage());
		blog('------------------');
		exit;
	}
	//bool $validLogin = authenticate_user1($uid, $password);
	
	
	
	blog('Connection successful and logged in! Looping through rows....');
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
		blog('Got a row: ' .  $rowEntry['UPLOAD_ID'] . ' | practice ID ' . $rowEntry['USER_GROUP_ID'] . ', filename is ' . $rowEntry['NAME']);
		try {
			//Make sure file is there. Create it if it doesn't exist.
			$filename = $folderName . $rowEntry['NAME'];
			blog('Determined filename to be ' . $filename);
			$handle = fopen($filename, 'a');
			
			if ($handle === false) {
				$err = error_get_last();
				fclose($handle);
				throw new RuntimeException('Error: Could not create the file ' . $filename . ': ' . $err['message']);
			}
			fclose($handle);
			
			blog('File opened successfully. Exporting blob into file....');
			// OCI-Lob object
			$ociLob = $rowEntry['BLOB_CONTENT'];
			
			if (!$ociLob->export($filename)) {
                $err = error_get_last();
				throw new RuntimeException('Could not write the BLOB content to the file ' . $filename . ': ' . $err['message']);
            }
			blog('Blob written to file successfully!');
			
			//blog('Wrote to the file!';
			
			//Now use our CCD API to do work on the file!
			//First login
			blog('Logging in as ' . $rowEntry['UPLOAD_UID'] . ' on ' . MyEnv::$CCD_INLOAD_HOST_IP . '...');
			
			//blog('<br>STR: operation=login&userId=' . $rowEntry['UPLOAD_UID'] . '&password=' . $rowEntry['UPLOAD_PW'] . '&practiceId=' . $rowEntry['USER_GROUP_ID'] . '---------';
			//continue;
			
			//try {
				$handle = curl_init();
				$postStr = 'operation=login&userId=' . $rowEntry['UPLOAD_UID'] . '&password=' . $rowEntry['UPLOAD_PW'] . '&practiceId=' . $rowEntry['USER_GROUP_ID'];//&filename=' . $rowEntry['NAME'] . '&filepath=' . $folderName;
				blog('process.php: Using post string ' . $postStr);

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
					curl_close($handle);
					throw new RuntimeException('CURL login error ' . $curlErrNo . ': ' . $curlErrMsg . ' [PHP said ' . $err['message'] . ']');
				}
				blog('cURL successful! Closing handle....');
				curl_close($handle);
				
				blog('Login: Result is `' . gettype($result) . ' ' . $result . '`');
			/*}
			catch (Exception $e) {
				blog('Got an exception: ' . $e->getMessage());// . ' ' . $e->getTraceAsString();
				exit;
			}*/
			blog('process.php: Finished cURL operation and got a result.');
			$resultStr = substr($result, 0, 2);
			
			if ($resultStr !== 'OK') {
				throw new RuntimeException('ERROR: Could not log in with that result! cannot continue! Got result ' . gettype($result) . ' ' . $result . '. resultStr = ' . $resultStr);
				continue;
			}
			
			$sessionId = substr($result, 7);
			blog('Login successful! Initializing and executing cURL. session ID is ' . $sessionId);
			
			$handle = curl_init();
			$postStr = 'operation=ccdupload&filename=' . $rowEntry['NAME'] . '&filepath=' . $folderName . '&sessionId=' . $sessionId;
			//$postStr = 'operation=ccdupload&filename=sample_cda.xml&filepath=uploads/&sessionId=' . $sessionId; //used to be kba6m0i3pqdbs56bfkp8uahhr2';
			blog('Calling ' . MyEnv::$BASE_URL . '/api/cerberus.php with post string ' . $postStr);

			curl_setopt($handle, CURLOPT_URL, 'http://127.0.0.1:80/analytics/api/cerberus.php');
			curl_setopt($handle, CURLOPT_HEADER, 0);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1); //Keep curl_exec quiet by stopping it from echoing output
			curl_setopt($handle, CURLOPT_POST, 4); 
			curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query(array('operation' => 'ccdupload',
																			'filename' => $rowEntry['NAME'], //was sample_cda.xml before.
																			'filepath' => 'uploads/',
																			'upload_id' => $rowEntry['UPLOAD_ID'],
																			'sessionId' => $sessionId, //used to be 'kba6m0i3pqdbs56bfkp8uahhr2',
																			'userGroupId' => $rowEntry['USER_GROUP_ID'], //should be 706
																			'IS_BATCH' => '1')
																		)
			);
			
			$result = curl_exec($handle);
			blog('We got a result: ' . gettype($result));
			
			
			if (!$result) {
				blog('Got an invalid cURL result.');
				$curlErrNo = curl_errno($handle);
				$curlErrMsg = curl_error($handle);
				curl_close($handle);
				
				$err = error_get_last();
				
				throw new RuntimeException('Could not do the cURL call for the file ' . $rowEntry['NAME'] . ': CURL error #' . $curlErrNo . ': ' . $curlErrMsg);
				//throw new RuntimeException('CURL error ' . $curlErrNo . ': ' . $curlErrMsg . ' [PHP said ' . $err['message'] . ']');
			}
			else {
				//Inload was successful! Update the uploads table.
				blog('Successfully imported ' . $rowEntry['NAME'] . '! Updating the uploads table...');
				setUploadTableStatus($rowEntry['UPLOAD_ID'], 'UPLOADED');
				
				//$err = oci_fetch_assoc($res);
				
				//$stid = oci_parse($conn, $sql);
				//$updateResult = oci_execute($stid);
				
				/*if (!$updateResult) {
					$err = oci_error();
					
				}*/
			}
			//curl_close($handle);
			//blog('Our cURL result is ' . gettype($result) . ' ' . $result);
			blog('-------------------------------------------------------------------------------------------------------------------');
			continue;
		}
		catch (Exception $e) {
			blog('ERROR: Could not upload file ' . $rowEntry['NAME'] . ': ' . $e->getMessage());
			blog('-------------------------------------------------------------------------------------------------------------------');
			setUploadTableStatus($rowEntry['UPLOAD_ID'], 'FAILED');
			continue;
		}
	}
	
	function setUploadTableStatus($rowID, $str) {
		$sql = "update UPLOAD set STATUS = '" . $str . "' where UPLOAD_ID = " . $rowID;
		$res = Dao::query($sql);
		
		if (oci_error($res)) {
			$err = oci_error($res);
			blog('Error: Could not update the UPLOAD table record ' . $rowID . ' to ' . $str . ': ' . $err['message']);
		}
		else {
			blog('UPLOAD table record ' . $rowID . ' updated to ' . $str . ' successfully!');
		}
	}
	
	//Cleanup
	
	//oci_free_statement($stid);
	//oci_close($conn);
?>