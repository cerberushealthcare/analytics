<?php
/**
 * Environment settings 
 */
abstract class Env {
  static $DB_NAME; 
  static $DB_SERVER;
  static $DB_USER;
  static $DB_PW;
  static $SFTP_PATH;  // path to SFTP folder 
  static $SEND_EMAIL;  // should send emails  
  static $LOG = false;  // should debug log 
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
  static function getEnv() {
    return static::$env;
  }
}
//
class Env_Local extends Env {
  static $env = self::ENV_LOCAL;
  static $DB_NAME = 'emrtest'; 
  static $DB_SERVER = 'localhost';
  static $DB_USER = 'webuser';
  static $DB_PW = 'click01';
  static $SFTP_PATH = 'C:\Program Files (x86)\Apache Software Foundation\Apache2.2\htdocs\clicktate\SFTP\TEST';
  static $SEND_EMAIL = false;
  static $LOG = true;
}
class Env_Test extends Env {
  static $env = self::ENV_TEST;
  static $DB_NAME = 'emrtest'; 
  static $DB_SERVER = 'localhost';
  static $DB_USER = 'webuser';
  static $DB_PW = 'click01';
  static $SFTP_PATH = 'D:\SFTP\TEST';
  static $SEND_EMAIL = true;
  static $LOG = true;
}
class Env_Production extends Env {
  static $env = self::ENV_PRODUCTION;
  static $DB_NAME = 'cert'; 
  static $DB_SERVER = 'localhost';
  static $DB_USER = 'webuser';
  static $DB_PW = 'click01';
  static $SFTP_PATH = 'D:\SFTP\PRODUCTION';
  static $SEND_EMAIL = true;
  static $LOG = false;
}
class Env_Papyrus_Prod extends Env {
  static $env = self::ENV_PAPYRUS_PROD;
  static $DB_NAME = 'cert'; 
  static $DB_SERVER = 'localhost';
  static $DB_USER = 'webuser';
  static $DB_PW = 'click01';
  static $SFTP_PATH = 'D:\SFTP\PRODUCTION';
  static $SEND_EMAIL = true;
  static $LOG = false;
}
