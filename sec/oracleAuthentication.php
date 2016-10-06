<?php
	//Can be found in php/data/LoginSession.php
	require_once 'config/MyEnv.php';
	require_once 'config/Environments.php';
	
	require_once 'php/data/rec/sql/dao/Logger.php'; //analytics/sec/logs/log.txt

function checkOracleLogin($uid, $pw) {
	try {
		$conn = oci_connect(MyEnv::$DB_USER, MyEnv::$DB_PW, MyEnv::$DB_SERVER . '/' . MyEnv::$DB_PROC_NAME);
		if(!$conn) {
			$err = oci_error();
			throw new RuntimeException($err['message']);	
		}

		$stid = oci_parse($conn, 'select FN_AUTHENTICATE_USER1(:userid, :pw) as "result" from dual');
		oci_bind_by_name($stid, ":userid", $uid);
		oci_bind_by_name($stid, ":pw", $pw);
		oci_execute($stid);

		$array = oci_fetch_assoc($stid);
		oci_free_statement($stid);
		oci_close($conn);
		
		//print_r($array);

		return $array['result'] == '1';
	}
	catch (Exception $e) {
		echo $e->getMessage();
		return false;
	}
}
  
  
if (checkOracleLogin('mm', 'clicktate1')) {
	echo 'Logged in successfully!';
}
else {
	echo 'Error with the log in...';
}
?>