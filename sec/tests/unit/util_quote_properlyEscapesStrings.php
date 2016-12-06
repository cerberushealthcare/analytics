<?php
	
	$testPassed = false;
	
	set_include_path('../../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	require_once('php/dao/_util.php');
	
	
	$oracleEscaped = quote("Column'Test", true);
	
	MyEnv::$IS_ORACLE = false;
	$sqlEscaped = quote("Column'Test", true);
	
	echo 'oracleEscaped: <b>' . $oracleEscaped . '</b> | sqlEscaped: <b>' . $sqlEscaped . '</b><br>';
	
	if ($oracleEscaped === "'Column''Test'" && $sqlEscaped == "'Column\'Test'") {
		$testPassed = true;
	}
	
	
	include('../postTestProcedures.php');
?>
