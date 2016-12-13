<?php
	error_reporting(E_ERROR);
	ini_set('display_errors', 1);
	
	set_include_path('../');
	ob_start('ob_gzhandler');
	require_once "php/data/LoginSession.php";
	require_once "inc/uiFunctions.php";
	require_once 'php/data/rec/erx/ErxStatus.php';
	require_once 'php/data/rec/sql/OrderEntry.php';
	require_once 'php/data/rec/sql/Meds.php';
	require_once 'php/data/rec/sql/Clients.php';
	require_once 'php/data/rec/sql/Documentation.php';
	require_once 'php/data/rec/sql/UserGroups.php';
	require_once 'php/data/rec/sql/LookupAreas.php';
	require_once 'php/c/health-maint/HealthMaint_Recs.php';
	require_once 'php/data/rec/sql/PortalUsers.php';
	require_once 'php/data/rec/sql/Templates_IolEntry.php';

	LoginSession::verify_forUser()->requires($login->Role->Patient->facesheet);
	//This test can be seen in action in face.php - this is what gathers the initialized JSON data from the bottom of the page.

	$testPassed = false;
	$constants = null;
	
	try {
		ob_start();
		CONSTANTS('Face');
		$constants = htmlspecialchars(ob_get_contents());
		ob_end_clean();
	}
	catch (Exception $e) {
		echo 'ERROR: ' . $e->getMessage();
	}
	
	echo $constants . '<br><br>';
	
	if (gettype($constants) == 'string' && 
	strpos($constants, 'C_MsgThread=') !== false &&
	strpos($constants, 'C_MsgPost=') !== false &&
	strpos($constants, 'C_MsgInbox=') !== false &&
	strpos($constants, 'C_UserRole=') !== false &&
	strpos($constants, 'C_DocStub=') !== false &&
	strpos($constants, 'C_TrackItem=') !== false &&
	strpos($constants, 'C_TrackEvent=') !== false &&
	strpos($constants, 'C_ProcResult=') !== false &&
	strpos($constants, 'C_Ipc=') !== false &&
	strpos($constants, 'C_Docs=') !== false &&
	strpos($constants, 'C_Address=') !== false &&
	strpos($constants, 'C_ScanIndex=') !== false &&
	strpos($constants, 'C_Areas=') !== false &&
	strpos($constants, 'C_PortalUser=') !== false &&
	strpos($constants, 'C_Users=') !== false &&
	strpos($constants, 'C_Question=') !== false &&
	strpos($constants, 'C_Test=') !== false &&
	strpos($constants, 'C_Action=') !== false &&
	strpos($constants, 'C_Diagnosis=') !== false &&
	strpos($constants, 'C_IpcHm=') !== false
	) $testPassed = true;
	
	include('postTestProcedures.php');
?>