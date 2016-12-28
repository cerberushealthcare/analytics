<?php
	
	set_include_path('../');
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
	$ugid = 706;
	$cid = 16665; //The client ID who will have the vitals inserted for. In our case Sharon C
		
	//$file->setContent(file_get_contents($rest->data['filepath'] . '/' . $rest->data['filename']));
	$file->setContent(file_get_contents('C:\www\clicktate\cert\sec\analytics\2645_65119_CABLE_SHARON_20161113142950.xml'));
	$file->setFilename('2645_65119_CABLE_SHARON_20161113142950.xml');

	if (gettype($file) !== 'object') {
		throw new RuntimeException("Can't import anything that isn't a <b>ClinicalFile</b> object. I got a(n) " . gettype($file));
	}
	
	
	
	try {
		//$result = ClinicalImporter::importFromFile($file);
		
		$ci = ClinicalImport::fromFile($file);
		echo 'ci is a ' . gettype($ci) . ' and the XML is ' . gettype($ci->Xml);

		
		//Vitals_Ci_Ccd::saveAll($ugid, $cid, $ci->Xml->getSection_Vitals());
		$alerts = $ci->Xml->getSection_Alerts();
		echo 'Alerts is a ' . gettype($alerts);
		echo '<br>';
		echo '<pre><span style="color: orange;">';
		var_dump($alerts);
		echo '</span></pre>';
	}
	catch (Exception $e) {
		if ($_POST['IS_BATCH']) echo 'ERROR importing the patient vitals: ' . $e->getMessage() . ' - continuing with import....';
		Logger::debug('ClinicalImport_Ccd::import: ERROR importing the patient vitals: ' . $e->getMessage() . ' - continuing with import....');
	}
	
?>