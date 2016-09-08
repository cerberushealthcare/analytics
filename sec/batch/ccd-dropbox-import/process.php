<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	$folderName = 'uploads/';
	
	set_include_path('../../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	require_once 'batch/_batch.php'; //Simply include this in order to use blog()
	
	blog('------------------');
	blog('Opening database....');
	//Queue the Oracle database for all records that need to be processed by looking at the 'STATUS' column.
	try {
		blog('Connecting to Oracle with DB user ' . MyEnv::$DB_USER . ' and server ' . MyEnv::$DB_SERVER . ' and proc name ' . MyEnv::$DB_PROC_NAME);
		$conn = oci_connect(MyEnv::$DB_USER, MyEnv::$DB_PW, MyEnv::$DB_SERVER . '/' . MyEnv::$DB_PROC_NAME);
		
		if(!$conn) {
			$err = oci_error();
			throw new RuntimeException($err['message']);	
		}
	}
	catch (Exception $e) {
		blog('Error opening the database: ' . $e->getMessage());
		blog('------------------');
		echo 'ERROR! ' . $e->getMessage();
		exit;
	}
	
	blog('Connection successful. Looping through rows....');
	$stid = oci_parse($conn, "select upload_id, user_group_id, practice_id, name, blob_content, status from upload where STATUS = 'UPLOAD REQUESTED'");
	oci_execute($stid);
	
	while (($rowEntry = oci_fetch_assoc($stid)) != false) {
		//For each record that we found, look at the BLOB content and make a file out of it.
		blog('Got a row: ' .  $rowEntry['UPLOAD_ID'] . ' | practice ID ' . $rowEntry['PRACTICE_ID']);
		try {
			//Make sure file is there. Create it if it doesn't exist.
			$filename = $folderName . $rowEntry['NAME'];
			blog('Determined filename to be ' . $filename);
			$handle = fopen($filename, 'a');
			
			if ($handle === false) {
				$err = error_get_last();
				throw new RuntimeException('Could not create the file ' . $filename . ': ' . $err['message']);
			}
			fclose($handle);
			
			blog('File opened successfully. Exporting blob into file....');
			// OCI-Lob object
			$ociLob = $rowEntry['BLOB_CONTENT'];
			
			if (!$ociLob->export($filename)) {
                $err = error_get_last();
				throw new RuntimeException('Could not write the BLOB content to the file ' . $filename . ': ' . $err['message']);
            }
			blog('Blob written to file successfully.');
			
			//echo 'Wrote to the file!';
			
			//Now use our CCD API to do work on the file!
			blog('Initializing and executing cURL....');
			
			$handle = curl_init();
			$postStr = 'operation=CCD_UPLOAD&filename=' . $rowEntry['NAME'] . '&filepath=' . $folderName;

			curl_setopt($handle, CURLOPT_URL, MyEnv::$BASE_URL . '/api/cerberus.php');
			curl_setopt($handle, CURLOPT_POSTFIELDS, $postStr);
			
			$result = curl_exec($handle);
			
			if (!$result) {
				$curlErrNo = curl_errno($handle);
				$curlErrMsg = curl_error($handle);
				curl_close($handle);
				
				$err = error_get_last();
				throw new RuntimeException('CURL error ' . $curlErrNo . ': ' . $curlErrMsg . ' [PHP said ' . $err['message'] . ']');
			}
			echo 'Our result is ' . gettype($result) . ' ' . $result . '<br>';
			curl_close($handle);
		}
		catch (Exception $e) {
			blog('Could not process row ID ' . $rowEntry['UPLOAD_ID'] . ': ' . $e->getMessage());
			blog('------------------');
			continue;
		}
	}
	
	//Cleanup
	
	oci_free_statement($stid);
	oci_close($conn);
?>