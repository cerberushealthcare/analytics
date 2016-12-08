<?php
	$folderName = 'C:/www/clicktate/cert/sec/analytics/uploads/';
	$upload_ID = 239; //Which row in the UPLOADS table do we want to make a BLOB from? This is used in a query.
	$testPassed = false;
	
	set_include_path('../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	require_once 'batch/_batch.php'; //Simply include this in order to use echo )
	require_once 'php/dao/_util.php'; //This has the openOracle() function in it that is needd for the Dao to work. We should probably put it in the Dao.php file at some point.
	require_once 'php/data/rec/sql/dao/Dao.php';
	//require_once 'php/data/rec/sql/dao/Logger.php'; //analytics/sec/logs/log.txt
	
	
	echo 'Opening database....' . '<br>';
	//Queue the Oracle database for all records that need to be processed by looking at the 'STATUS' column.
	try {
		echo 'Connecting to Oracle with DB user ' . MyEnv::$DB_USER . ', password ' . MyEnv::$DB_USER . ' and server ' . MyEnv::$DB_SERVER . ' and proc name ' . MyEnv::$DB_PROC_NAME;
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
	
	
	
	echo 'Connection successful and logged in! Looping through rows....';
	$stid = oci_parse($conn, "select upload.upload_id, upload.user_group_id, upload.name, upload.blob_content, upload.status, 
			user_groups.upload_uid, user_groups.upload_pw, user_groups.user_group_id
			from upload
			left join user_groups
			on upload.user_group_id = user_groups.USER_GROUP_ID
			where UPLOAD_ID = " . $upload_ID);
	oci_execute($stid);
	
	//set_time_limit(10800); //3 hours. We could end up having to process hundreds of xml files.
	while (($rowEntry = oci_fetch_assoc($stid)) != false) {
		//For each record that we found, look at the BLOB content and make a file out of it.
		echo 'Got a row: ' .  $rowEntry['UPLOAD_ID'] . ' | practice ID ' . $rowEntry['USER_GROUP_ID'] . ', filename is ' . $rowEntry['NAME'] . '<br>';
		try {
			//Make sure file is there. Create it if it doesn't exist.
			$filename = $folderName . $rowEntry['NAME'];
			echo 'Determined filename to be ' . $filename . '<br>';
			$handle = fopen($filename, 'a');
			
			if ($handle === false) {
				$err = error_get_last();
				fclose($handle);
				throw new RuntimeException('Error: Could not create the file ' . $filename . ': ' . $err['message']);
			}
			fclose($handle);
			
			echo 'File opened successfully. Exporting blob into file....' . '<br>';
			// OCI-Lob object
			$ociLob = $rowEntry['BLOB_CONTENT'];
			
			if (!$ociLob->export($filename)) {
                $err = error_get_last();
				throw new RuntimeException('Could not write the BLOB content to the file ' . $filename . ': ' . $err['message']);
            }
			echo 'Blob written to file successfully!' . '<br>';
			$testPassed = true;
		}
		catch (Exception $e) {
			echo 'ERROR: Could not make a BLOB file ' . $rowEntry['NAME'] . ': ' . $e->getMessage() . '<br>';
		}
	}
	
	include('../postTestProcedures.php');
?>