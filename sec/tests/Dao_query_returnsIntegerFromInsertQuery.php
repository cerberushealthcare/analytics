<?php
	$testPassed = false;

	set_include_path('../..');

	require_once 'config/Environments.php';
	require_once 'config/MyEnv.php';
	//require_once 'batch/_batch.php'; //Simply include this in order to use blog()
	require_once 'php/data/rec/sql/dao/Dao.php';
	
	
	
	echo 'Opening database....' . '<br>';
	//Queue the Oracle database for all records that need to be processed by looking at the 'STATUS' column.
	try {
		echo 'Connecting to Oracle with DB user ' . MyEnv::$DB_USER . ', password ' . MyEnv::$DB_USER . ' and server ' . MyEnv::$DB_SERVER . ' and proc name ' . MyEnv::$DB_PROC_NAME . '<br>';
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
	
	echo 'Getting insert ID...' . '<br>';
	
	$query = "INSERT INTO addresses (ADDRESS_ID,TABLE_CODE,TABLE_ID,TYPE,ADDR1,ADDR2,CITY,STATE,ZIP,COUNTRY,PHONE1,PHONE1_TYPE,PHONE2,PHONE2_TYPE,PHONE3,PHONE3_TYPE,EMAIL1,EMAIL2,NAME)
    VALUES(null,'C',null,'0','1187 Test Dr.',null,'Lexington','KY','40517',null,'(   ) 309-1097','0',null,null,null,null,null,null,null) returning ADDRESS_ID into :returnVal";
	
	$insertId = Dao::insert($query);
	
	echo 'Insert ID is ' . gettype($insertId) . ' ' . $insertId;
	
	if (gettype($insertId) == 'integer') $testPassed = true;
	
	
	include('postTestProcedures.php');
?>