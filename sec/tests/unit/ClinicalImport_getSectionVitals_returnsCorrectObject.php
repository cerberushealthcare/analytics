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
	//$_POST['IS_BATCH'] = 1; //We need to do this so that _SqlRec does not check $_GET for a user group ID.
		
	//$file->setContent(file_get_contents($rest->data['filepath'] . '/' . $rest->data['filename']));
	$file->setContent(file_get_contents('C:\www\clicktate\cert\sec\analytics\2645_65119_CABLE_SHARON_20161113142950.xml'));
	$file->setFilename('2645_65119_CABLE_SHARON_20161113142950.xml');
	
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