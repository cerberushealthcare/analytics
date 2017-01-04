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
	$ugid = 2645; //This will go directly into the DATA_VITALS table as the UGID column.
	$cid = 16665; //The client ID who will have the vitals inserted for. In our case Sharon C. This goes directly into the DATA_VITALS table as the client ID column. The CCD batch import process already figures this out by itself (by inserting the new client into the clients table and grabbing the insert ID) - however in our test here we have to somehow define it.....
	
	//The login dilemma: In _SqlRec we have the code if (!is_null($login)) $criteria->authenticateAsCriteria();, which basically means if we are running a batch job like this one,
	//and thus the $login global doesn't exist, we should skip the query authentication process. There's probably a better way to do this. Since we have this check we do not need
	//to do the curl API login below.
	
	
	//$_POST['IS_BATCH'] = 1; //We need to do this so that _SqlRec does not check $_GET for a user group ID.
		
	//$file->setContent(file_get_contents($rest->data['filepath'] . '/' . $rest->data['filename']));
	$path = 'C:\www\clicktate\cert\sec\analytics\\';
	
	//--------------Batch test-----------------
	/*$fileList = array(
		array('2645_38224_CRAFT_PHYLLIS_20161113142235.xml', 16689),
		array('uploads2645_130084_SNYDER_RUTH_20161113160014.xml', 13575),
		array('uploads2645_130085_AGEE_NANNIE_20161113160015.xml', 13576),
		array('uploads2645_130088_CARLSON_IVAR_20161113160016.xml', 13577),
		array('uploads2645_130089_FAIN_BARBARA_20161113160016.xml', 13578),
		array('uploads2645_129525_KURICH_STEPAN_20161113155624.xml', 13229),
		array('uploads2645_130198_PATZ_ULA_20161113160100.xml', 13636),
		array('uploads2645_131857_MCNEELY_MICHAEL_20161113161005.xml', 14366),
		array('uploads2645_129639_KNOX_SHIRLEY_20161113155708.xml', 13290),
		array('uploads2645_131051_WHITT_LISA_20161113160553.xml', 14040),
		array('uploads2645_130409_ESCUDERO_RAUL_20161113160228.xml', 13768),
		array('uploads2645_129430_HOLTS_CRYSTAL_20161113155544.xml', 13170)
	);
	
	foreach ($fileList as &$fileEntry) {
		echo 'Importing ' . $path . ' ' . $fileEntry[0] . '.....<br>';
		$file->setContent(file_get_contents($path . $fileEntry[0]));
		$file->setFilename($fileEntry[0]);
		testImport($file, $ugid, $fileEntry[1]);
		echo '--------------------------------';
	}*/
	
	//-----------------Single file test
	$file->setContent(file_get_contents($path . '2645_38224_CRAFT_PHYLLIS_20161113142235.xml'));
	$file->setFilename('2645_38224_CRAFT_PHYLLIS_20161113142235.xml');
	testImport($file, $ugid, 1689); //last value is the client ID (found in the clients table by searching for the patient name)
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//------------------------------------Do the process ------------------------
	
	function testImport($file, $ugid, $cid) {
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
			
			if (is_null($vitals)) {
				echo 'Vitals result is null - no vitals section found in this CCD.';
			}
			else {
				if (get_class($vitals) !== 'Ccd_Section_Vitals_Sql') {
					throw new RuntimeException('Invalid vitals section! Got "' . get_class($vitals) . '" ' . print_r($vitals, true));
				}
			}
			$testPassed = true;
			
			//This should NOT be in here, just using it temporarily for debugging purposes.
			try {
				echo 'Saving all with ugid ' . $ugid;
				Vitals_Ci_Ccd::saveAll($ugid, $cid, $vitals); // $vitals is the same as doing $ci->Xml->getSection_Vitals(), which is what we actually do in the code base
			}
			catch (Exception $e) {
				throw new RuntimeException('ClinicalImport_importsVitals test: Error saving the vitals for client ID ' . $cid . ':' . $e->getMessage());
			}
		}
		catch (Exception $e) {
			$testPassed = false;
			Logger::debug('ClinicalImport_importVitals test: ERROR importing the patient vitals for client ID ' . $cid . ': ' . $e->getMessage() . ' - continuing with import....');
		}
	}
	
	include('../postTestProcedures.php');
?>