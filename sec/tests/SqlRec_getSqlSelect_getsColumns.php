<?php
	error_reporting(E_ERROR);
	ini_set('display_errors', 1);
	
	set_include_path('../');
	require_once "php/data/rec/sql/_SqlRec.php";
	require_once "php/data/rec/sql/UserLogins.php";
    require_once "php/data/rec/sql/Diagnoses.php";
	
	$testPassed = true;
	
	 $c = UserLogin::asCriteria('mm'); //Must make a criteria object before doing queries. Most files in php/data/rec/sql have a method called asCriteria()?
     //$me = static::fetchOneBy($c); //In the code we usually do a call to fetchOneBy like this....
	 $ci = $c->getRecsFromCriteria(); //The $c parameter on the last line is what we use as the getRecsFromCriteria container. So we could do something like this:
	 /*
	  $c = static::fetchOneBy($criteriaObject);
	  $criteriaObject->getRecsFromCriteria();
	 */
	 $infos = SqlRec::buildSqlSelectInfos_Test($ci); //Normally buildSqlSelectInfos is only called in _SqlRec.php and is a protected method.
	 $fields = implode(', ', array_filter($infos['fields']));//We may need this: ($asCount) ? 'COUNT(*)' : implode(', ', array_filter($infos['fields']));
		//This will make $fields a string containing ALL of the needed SELECTS. This is a seam point.
		
	echo 'Got fields as ' . $fields . '<br>';
	
	if ($fields === 'T0.USER_ID AS "UserLogin.T0.0", T0.UID_ AS "UserLogin.T0.1", T0.PW AS "UserLogin.T0.2", T0.NAME AS "UserLogin.T0.3", T0.ADMIN AS "UserLogin.T0.4", T0.SUBSCRIPTION AS "UserLogin.T0.5", T0.ACTIVE AS "UserLogin.T0.6", T0.REG_ID AS "UserLogin.T0.7", T0.TRIAL_EXPDT AS "UserLogin.T0.8", T0.USER_GROUP_ID AS "UserLogin.T0.9", T0.USER_TYPE AS "UserLogin.T0.10", T0.LICENSE_STATE AS "UserLogin.T0.11", T0.LICENSE AS "UserLogin.T0.12", T0.DEA AS "UserLogin.T0.13", T0.NPI AS "UserLogin.T0.14", T0.EMAIL AS "UserLogin.T0.15", T0.EXPIRATION AS "UserLogin.T0.16", T0.EXPIRE_REASON AS "UserLogin.T0.17", T0.PW_EXPIRES AS "UserLogin.T0.18", T0.TOS_ACCEPTED AS "UserLogin.T0.19", T0.ROLE_TYPE AS "UserLogin.T0.20", T0.MIXINS AS "UserLogin.T0.21", T0.RESET_HASH AS "UserLogin.T0.22", T1.USER_GROUP_ID AS "UserGroup_Login.T1.0", T1.NAME AS "UserGroup_Login.T1.1", T1.USAGE_LEVEL AS "UserGroup_Login.T1.2", T1.EST_TZ_ADJ AS "UserGroup_Login.T1.3", T1.SESSION_TIMEOUT AS "UserGroup_Login.T1.4", T1.DEMO AS "UserGroup_Login.T1.5", T2.ADDRESS_ID AS "AddressUserGroup_Login.T2.0", T2.TABLE_CODE AS "AddressUserGroup_Login.T2.1", T2.TABLE_ID AS "AddressUserGroup_Login.T2.2", T2.TYPE AS "AddressUserGroup_Login.T2.3", T2.ADDR1 AS "AddressUserGroup_Login.T2.4", T2.ADDR2 AS "AddressUserGroup_Login.T2.5", T2.ADDR3 AS "AddressUserGroup_Login.T2.6", T2.CITY AS "AddressUserGroup_Login.T2.7", T2.STATE AS "AddressUserGroup_Login.T2.8", T2.ZIP AS "AddressUserGroup_Login.T2.9", T2.COUNTRY AS "AddressUserGroup_Login.T2.10", T2.PHONE1 AS "AddressUserGroup_Login.T2.11", T2.PHONE1_TYPE AS "AddressUserGroup_Login.T2.12", T2.PHONE2 AS "AddressUserGroup_Login.T2.13", T2.PHONE2_TYPE AS "AddressUserGroup_Login.T2.14", T2.PHONE3 AS "AddressUserGroup_Login.T2.15", T2.PHONE3_TYPE AS "AddressUserGroup_Login.T2.16", T2.EMAIL1 AS "AddressUserGroup_Login.T2.17", T2.EMAIL2 AS "AddressUserGroup_Login.T2.18", T2.NAME AS "AddressUserGroup_Login.T2.19", T2.COUNTY AS "AddressUserGroup_Login.T2.20", T3.USER_ID AS "BillInfo_Login.T3.0", T3.EXP_MONTH AS "BillInfo_Login.T3.1", T3.EXP_YEAR AS "BillInfo_Login.T3.2", T3.LAST_BILL_STATUS AS "BillInfo_Login.T3.3", T4.USER_ID AS "NcUser_Login.T4.0", T4.USER_TYPE AS "NcUser_Login.T4.1", T4.ROLE_TYPE AS "NcUser_Login.T4.2", T4.PARTNER_ID AS "NcUser_Login.T4.3", T4.NAME_LAST AS "NcUser_Login.T4.4", T4.NAME_FIRST AS "NcUser_Login.T4.5", T4.NAME_MIDDLE AS "NcUser_Login.T4.6", T4.NAME_PREFIX AS "NcUser_Login.T4.7", T4.NAME_SUFFIX AS "NcUser_Login.T4.8", T4.FREEFORM_CRED AS "NcUser_Login.T4.9"') {
		echo '<b><span style="color: green;">Passed!</span></b>';
	}
	else {
		echo '<b><span style="color: red;">FAILED!</span></b>';
		$testPassed = false;
	}
	
	echo '<hr>';
	
	 $c = SessionDiagnosis::asCriteria('mm'); //To use a different table, all we have to do is change this one line. The rest stays the same. We got "SessionDiagnosis" by looking at data/rec/sql/Diagnoses.php and looking for the class that has the asCriteria() method in it.
	 $ci = $c->getRecsFromCriteria();
	 $infos = SqlRec::buildSqlSelectInfos_Test($ci);
	 $fields = implode(', ', array_filter($infos['fields']));
		
	echo 'Got fields as ' . $fields . '<br>';
	
	if ($fields === 'T0.DATA_DIAGNOSES_ID AS "SessionDiagnosis.T0.0", T0.USER_GROUP_ID AS "SessionDiagnosis.T0.1", T0.CLIENT_ID AS "SessionDiagnosis.T0.2", T0.SESSION_ID AS "SessionDiagnosis.T0.3", T0.DATE_ AS "SessionDiagnosis.T0.4", T0.PAR_UID AS "SessionDiagnosis.T0.5", T0.TEXT AS "SessionDiagnosis.T0.6", T0.PAR_DESC AS "SessionDiagnosis.T0.7", T0.ICD AS "SessionDiagnosis.T0.8", T0.ICD10 AS "SessionDiagnosis.T0.9", T0.ACTIVE AS "SessionDiagnosis.T0.10", T0.DATE_UPDATED AS "SessionDiagnosis.T0.11", T0.DATE_CLOSED AS "SessionDiagnosis.T0.12", T0.STATUS AS "SessionDiagnosis.T0.13", T0.SNOMED AS "SessionDiagnosis.T0.14", T0.DATE_RECON AS "SessionDiagnosis.T0.15", T0.RECON_BY AS "SessionDiagnosis.T0.16"') {
		echo '<b><span style="color: green;">Passed!</span></b>';
	}
	else {
		echo '<b><span style="color: red;">FAILED!</span></b>';
		$testPassed = false;
	}
	
	echo '<br><br><br>';
	
	include('postTestProcedures.php');
?>