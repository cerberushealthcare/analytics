<?php
	$testPassed = false;
	$file = new ClinicalFile;
		
	//$file->setContent(file_get_contents($rest->data['filepath'] . '/' . $rest->data['filename']));
	$file->setContent(file_get_contents('C:\www\clicktate\cert\sec\analytics\2645_65119_CABLE_SHARON_20161113142950.xml'));
	$file->setFilename('2645_65119_CABLE_SHARON_20161113142950.xml');

	if (gettype($file) !== 'object') {
		throw new RuntimeException("Can't import anything that isn't a <b>ClinicalFile</b> object. I got a(n) " . gettype($file));
	}
	
	
	
	try {
		//$result = ClinicalImporter::importFromFile($file);
		
		$ci = ClinicalImport::fromFile($file);
		echo 'ci is a ' . gettype($ci) . ' ' . $ci . '<br>';
		//try {
		  $cid = $ci->import($cid);
		  //-----Begin contents of Scanning::saveClinicalXml (found in php/data/rec/sql/Scanning.php)
		   $index = ScanIndex_Xml::from($file->filename, $cid);
	
			if ($_POST['IS_BATCH']) {
				$groupId = $_POST['userGroupId'];
			}
			else {
				$groupId = $login->userGroupId;
			}
			$testPassed = true;
			/*$index->save($groupId);
	
		  //-----End contents of Scanning:saveClinicalXml
		  
		  Proc_CcdImported::record($cid);
		} catch (DuplicateNameBirth $e) {
		  $ci->Client = $e->data;
		  throw new DupeImportPatient($ci);
		}
		return $ci;*/
	}
	catch (Exception $e) {
		echo 'ERROR: ' . $e->getMessage();
	}
	
	include('postTestProcedures.php');
?>