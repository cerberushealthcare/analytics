<?
require_once "server.php";
//
p_r('here');
LoginSession::verify_forUser();
global $login;
p_r($login);
