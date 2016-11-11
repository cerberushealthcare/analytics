<?php
/**
 * Environment settings
 */
abstract class Env {
  static $DB_NAME;
  static $DB_SERVER;
  static $DB_USER;
  static $DB_PW;
  static $BASE_PATH;  // sec path
  static $SFTP_PATH;  // path to SFTP folder
  static $SEND_EMAIL;  // should send emails
  static $BASE_URL;
  static $PDF_URL;
  static $CERBERUS_APEX_APP;  // deprecated
  static $SALUTOPIA_URL = 'https://212.54.145.110:11009';
  static $GD_URL = 'http://192.186.225.198';
  static $LOG = false;  // should debug log
  static $LOG_PATH;
  static $BATCH = false;  // set true by _batch.php processes
  static $ENCRYPT = false;
  static $BATCH_MYSQL = "C:\Program Files\MySQL\MySQL Server 5.1\bin\mysql.exe";
  //
  static $TOS_VERSION = '1.2';
  static $TOS_DATE = '2012-12-21';
  static $BAA_VERSION = '1.1';
  //
  const ENV_LOCAL = 1;
  const ENV_TEST = 2;
  const ENV_PRODUCTION = 9;
  const ENV_PAPYRUS_LOCAL = 11;
  const ENV_PAPYRUS_TEST = 12;
  const ENV_PAPYRUS_PROD = 19;
  //
  protected static $env;
  //
  static function url($path) {
    return static::$BASE_URL . $path;
  }
  static function getEnv() {
    return static::$env;
  }
  static function isLocal() {
    return static::$env == static::ENV_LOCAL;
  }
  static function getMcsk() {
    return sha1('ENV' . static::getEnv());
  }
  static function getMchk() {
    return 'NaCl';
  }
}
//
class Env_Local extends Env {
  static $env = self::ENV_LOCAL;
  static $DB_NAME = 'emrtest';
  static $DB_SERVER = 'localhost';
  static $DB_USER = 'webuser';
  static $DB_PW = 'click01';
  static $BASE_PATH = 'C:\Program Files (x86)\Apache Software Foundation\Apache2.2\htdocs\clicktate\sec';
  static $SFTP_PATH = 'C:\Program Files (x86)\Apache Software Foundation\Apache2.2\htdocs\clicktate\SFTP\TEST';
  static $LOG_PATH = 'C:\Program Files (x86)\Apache Software Foundation\Apache2.2\htdocs\clicktate\sec\logs';
  static $SEND_EMAIL = false;
  static $LOG = true;
  static $BASE_URL = 'http://localhost/clicktate/sec/';
  static $PDF_URL = 'http://localhost/clicktate/sec/';
  static $CERBERUS_APEX_APP = '307';
  static $ENCRYPT = true;
  static $BATCH_MYSQL = "C:\Program Files (x86)\MySQL\MySQL Server 5.1\bin\mysql.exe";
}
class Env_Test extends Env {
  static $env = self::ENV_TEST;
  static $DB_NAME = 'emrtest';
  static $DB_SERVER = 'localhost';
  static $DB_USER = 'webuser';
  static $DB_PW = 'click01';
  static $BASE_PATH = 'D:\www\test-clicktate\sec';
  static $SFTP_PATH = 'D:\SFTP\TEST';
  static $LOG_PATH = 'D:\www\test-clicktate\sec\logs';
  static $SEND_EMAIL = true;
  static $LOG = true;
  static $BASE_URL = 'http://test.clicktate.com/sec/';
  static $PDF_URL = 'http://127.0.0.1:88/sec/';
  static $CERBERUS_APEX_APP = '307';
  static $ENCRYPT = true;
}
class Oracle_Test extends Env {
  static $env = self::ENV_PRODUCTION;
  static $DB_NAME = 'emrtest';
  static $DB_SERVER = '208.187.161.81';
  static $DB_USER = 'emrtest';
  static $DB_PW = 'emrtest';
  static $IS_ORACLE = true;
  static $DB_PROC_NAME = 'pdborcl';
  static $BASE_PATH = 'C:\www\clicktate\cert\sec';
  static $SFTP_PATH = 'C:\SFTP\PRODUCTION';
  static $SEND_EMAIL = true;
  static $LOG = false;
  static $LOG_PATH = 'C:\Users\Darrin DeYoung\Documents\EasyPHP-Devserver-16.1\eds-www\analytics\sec\logs';
  static $BASE_URL = 'https://www.clicktate.com/cert/sec/';
  static $PDF_URL = 'https://127.0.0.1/cert/sec/';
  static $CERBERUS_APEX_APP = '307';
  static $CCD_INLOAD_HOST_IP = '208.187.161.81'; //When completely live this has to be 192.168.1.81
  static $ENCRYPT = false;
}
class Oracle_Production extends Env {
  static $env = self::ENV_PRODUCTION;
  static $DB_NAME = 'cin';
  static $DB_SERVER = '208.187.161.81';
  static $DB_USER = 'cin';
  static $DB_PW = 'Cin123';
  static $IS_ORACLE = true;
  static $DB_PROC_NAME = 'pdborcl';
  static $BASE_PATH = 'C:\www\clicktate\cert\sec';
  static $SFTP_PATH = 'C:\SFTP\PRODUCTION';
  static $SEND_EMAIL = true;
  static $LOG = true;
  static $LOG_PATH = 'C:\Users\Darrin DeYoung\Documents\EasyPHP-Devserver-16.1\eds-www\analytics\sec\logs';
  static $BASE_URL = 'http://127.0.0.1/analytics';
  static $PDF_URL = 'https://127.0.0.1/analytics/cert/sec/';
  static $CCD_INLOAD_HOST_IP = '208.187.161.81'; //When completely live this has to be 192.168.1.81
  static $CERBERUS_APEX_APP = '307';
  static $ENCRYPT = false;
}
class Analytics extends Env {
  static $env = self::ENV_PRODUCTION;
  static $DB_NAME = 'emrtest';
  static $DB_SERVER = '208.187.161.70';
  static $DB_USER = 'webuser';
  static $DB_PW = 'click01';
  static $IS_ORACLE = false;
  static $DB_PROC_NAME = '';
  static $BASE_PATH = 'C:\Users\Darrin DeYoung\Documents\EasyPHP-Devserver-16.1\eds-www\analytics\sec';
  static $SFTP_PATH = 'C:\SFTP\PRODUCTION';
  static $SEND_EMAIL = true;
  static $LOG = true;
  static $LOG_PATH = 'C:\Users\Darrin DeYoung\Documents\EasyPHP-Devserver-16.1\eds-www\analytics\sec\logs';
  static $BASE_URL = 'https://www.clicktate.com/cert/sec/';
  static $PDF_URL = 'https://127.0.0.1/cert/sec/';
  static $CERBERUS_APEX_APP = '307';
  static $CCD_INLOAD_HOST_IP = '208.187.161.81'; //When completely live this has to be 192.168.1.81
  static $ENCRYPT = false;
}
class Env_Production extends Env {
  static $CCD_INLOAD_HOST_IP = '192.168.1.81';
  static $env = self::ENV_PRODUCTION;
  static $DB_NAME = 'cert';
  static $DB_SERVER = 'localhost';
  //static $DB_SERVER = '208.187.161.81';
  static $DB_USER = 'webuser';
  static $DB_PW = 'click01';
  static $BASE_PATH = 'D:\www\clicktate\cert\sec';
  static $SFTP_PATH = 'D:\SFTP\PRODUCTION';
  static $SEND_EMAIL = true;
  static $LOG = false;
  static $LOG_PATH = 'D:\www\clicktate\cert\sec\logs';
  static $BASE_URL = 'https://www.clicktate.com/cert/sec/';
  static $PDF_URL = 'https://127.0.0.1/cert/sec/';
  static $CERBERUS_APEX_APP = '307';
  static $ENCRYPT = false;
}
class Env_Mirror_Prod extends Env_Production {
  //static $SFTP_PATH = 'D:\SFTP\PRODUCTION';
  static $SFTP_PATH = 'C:\Dropbox (Papyrus CIN)\SFTP\PRODUCTION';
  //static $BASE_PATH = 'C:\www\clicktate\cert\sec';
  static $BASE_PATH = 'C:\Dropbox (Papyrus CIN)\user_folders_root';
  static $LOG_PATH = 'C:\www\clicktate\cert\sec\logs';
  static $PDF_URL = 'http://127.0.0.1/cert/sec/';
  //static $PDF_URL = 'http://127.0.0.1/ufolders/';
  static $LOG = false;
  static $DB_SERVER = '192.168.1.69';
 // static $DB_SERVER = '192.168.36.69';
  static $PDO_DB_NAME = 'cert';
  static $PDO_DB_TYPE = 'mysql';
  static $CCD_INLOAD_HOST_IP = '208.187.161.81'; //When completely live this has to be 192.168.1.81
}
class Env_QA extends Env_Production {
  static $BASE_PATH = 'D:\www\clicktate\qa\sec';
  static $LOG_PATH = 'D:\www\clicktate\qa\sec\logs';
  static $BASE_URL = 'https://www.clicktate.com/qa/sec/';
  static $PDF_URL = 'https://127.0.0.1/qa/sec/';
}
/* Deprecated */
class Env_Papyrus_Prod extends Env {
  static $env = self::ENV_PAPYRUS_PROD;
  static $DB_NAME = 'cert';
  static $DB_SERVER = 'localhost';
  static $DB_USER = 'webuser';
  static $DB_PW = 'click01';
  static $SFTP_PATH = 'D:\SFTP\PRODUCTION';
  static $SEND_EMAIL = true;
  static $LOG = false;
  static $BASE_URL = 'https://www.clicktate.com/papyrus/sec/';
  static $PDF_URL = 'https://127.0.0.1/papyrus/sec/';
}
