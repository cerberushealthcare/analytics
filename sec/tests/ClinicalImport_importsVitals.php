**Make sure you log into analytics before running this test! We should put a login procedure in this test so that we don't need to do this...
<br>
<?php
	
	set_include_path('../');
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
		$vitals = $ci->Xml->getSection_Vitals();
		echo 'vitals is a ' . gettype($vitals);
		echo '<br>';
		echo '<pre><span style="color: orange;">';
		var_dump($vitals);
		echo '</span></pre>';
		
		if (gettype($vitals->text) !== 'object') {
			throw new RuntimeException('Invalid vitals section: Got ' . gettype($vitals) . ' ' . $vitals);
		}
		$testPassed = true;
		
		try {
			LoginSession::verify_forServer(); //This may have screwed us up and gave us the feedback-less error
		}
		catch (Exception $e) {
			throw new RuntimeException('ClinicalImport_importsVitals test: Error verifying for the server: ' . $e->getMessage() . ' (Try logging into Analytics manually?)');
		}
		
		try {
			
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
	
	
	include('postTestProcedures.php');
?>