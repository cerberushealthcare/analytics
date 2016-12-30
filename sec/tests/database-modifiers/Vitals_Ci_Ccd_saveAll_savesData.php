<?php
	
	set_include_path('../../');
	ob_start('ob_gzhandler');
	require_once 'server.php';
	require_once 'php/c/patient-import/clinical-xml/ClinicalImporter.php';
	require_once 'php/c/patient-import/clinical-xml/ClinicalImport_Sql.php';
	require_once 'php/c/patient-import/clinical-xml/ClinicalImport_Ccd.php';
	require_once 'php/c/patient-import/clinical-xml/ClinicalImport_Ccr.php';
	require_once 'php/data/rec/group-folder/GroupFolder_ClinicalImport.php';
	require_once 'php/data/xml/clinical/ccd/ContinuityCareDocument.php';
	require_once 'php/data/xml/clinical/ccr/ContinuityCareRecord.php';
	require_once 'php\data\rec\sql\Vitals.php';
	$testPassed = false;
	$file = new ClinicalFile;
	$ugid = 2645;
	$cid = 16665; //The client ID who will have the vitals inserted for. In our case Sharon C
	
	//The login dilemma: In _SqlRec we have the code if (!is_null($login)) $criteria->authenticateAsCriteria();, which basically means if we are running a batch job like this one,
	//and thus the $login global doesn't exist, we should skip the query authentication process. There's probably a better way to do this. Since we have this check we do not need
	//to do the curl API login below.
	
	
	//$_POST['IS_BATCH'] = 1; //We need to do this so that _SqlRec does not check $_GET for a user group ID.
		
	//$file->setContent(file_get_contents($rest->data['filepath'] . '/' . $rest->data['filename']));
	$file->setContent(file_get_contents('C:\www\clicktate\cert\sec\analytics\2645_65119_CABLE_SHARON_20161113142950.xml'));
	$file->setFilename('2645_65119_CABLE_SHARON_20161113142950.xml');
	
	
	//------------------------------------------ First do an API login. This prevents Access not allowed custom exceptions.-----------------
	
	/*echo 'Logging in as mm on ' . MyEnv::$CCD_INLOAD_HOST_IP . '...' . '<br>';
	
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
	echo 'Login successful! Initializing and executing cURL. session ID is ' . $sessionId . '<br>';*/
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//------------------------------------Do the process ------------------------
	
	try {
		//$result = ClinicalImporter::importFromFile($file);
		
		if (gettype($file) !== 'object') {
			throw new RuntimeException("Can't import anything that isn't a <b>ClinicalFile</b> object. I got a(n) " . gettype($file));
		}
		
		$ci = ClinicalImport::fromFile($file);
		echo 'ci is a ' . gettype($ci) . ' and the XML is ' . gettype($ci->Xml);
		
		//Vitals_Ci_Ccd::saveAll($ugid, $cid, $ci->Xml->getSection_Vitals());
		$vitals = $ci->Xml->getSection_Vitals();
		echo 'vitals is a ' . gettype($vitals);
		echo '<br>';
		echo '<pre><span style="color: orange;">';
		var_dump($vitals);
		echo '</span></pre>';
		
		if (get_class($vitals) !== 'Ccd_Section_Vitals_Sql') {
			throw new RuntimeException('Invalid vitals section! Got "' . get_class($vitals) . '" ' . print_r($vitals, true));
		}
		$testPassed = true;
		
		//This should NOT be in here, just using it temporarily for debugging purposes.
		try {
			echo 'Saving all with ugid ' . $ugid;
			Vitals_Ci_Ccd::saveAll($ugid, $cid, $vitals); // $vitals is the same as doing $ci->Xml->getSection_Vitals(), which is what we actually do in the code base
		}
		catch (Exception $e) {
			throw new RuntimeException('ClinicalImport_importsVitals test: Error saving the vitals: ' . $e->getMessage());
		}
	}
	catch (Exception $e) {
		$testPassed = false;
		Logger::debug('ClinicalImport_importVitals test: ERROR importing the patient vitals: ' . $e->getMessage() . ' - continuing with import....');
	}
	
	
	include('../postTestProcedures.php');
?>