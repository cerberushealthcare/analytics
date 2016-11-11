<?php
	//Verify that when we make something from _Rec, we get the correct query field names.
	//In Oracle / SQL there are many words that we can't use in our queries and we want to make sure we don't try using any of them because it'll break the query.
	$testPassed = false;
	set_include_path('../../');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	require_once 'php/data/rec/sql/_SqlRec.php';
	
	$fields = array('quid' => 'quid',
					'index' => 'index',
					'rowid' => 'rowid',
					'expires' => 'expires'
				);
	
	foreach ($fields as $key => $value) {
		//echo 'getSqlInsert: Converting word ' . $value . '<br>';
		//$field = SqlRec::convertReservedOracleWords($field, true);
		$converted = SqlRec::convertReservedOracleColumnWords($value, true, array('rowid'));
		unset($fields[$key]);
		$fields[$converted] = $converted;
		//echo 'Changed the word to ' . $fields[$converted] . '<br>';
	}
	
	//echo 'final array: ' . print_r($fields, true);
	
	if (array_key_exists('index_', $fields)) {
		if ($fields['index_'] == 'index_' && array_key_exists('rowid', $fields)) $testPassed = true;
	}
	
	include('postTestProcedures.php');
?>