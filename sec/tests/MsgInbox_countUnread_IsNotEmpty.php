<?php

set_include_path('../');

require_once 'config/MyEnv.php';
require_once 'php/data/rec/sql/_MessagingRecs.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/email/Email.php';

require_once "php/data/rec/sql/Messaging.php";
require_once "php/data/rec/sql/Messaging_DocStubReview.php";
require_once "php/data/rec/sql/HL7_Labs.php";

$testPassed = false;

echo 'Test currently fails! Needs an integer.<br>';

$unread = MsgInbox::countUnread('mm'); //This MUST BE AN INTEGER, not a string....

echo 'Unread is ' . gettype($unread) . ' ' . $unread . '<br>';

if (strlen($unread) > 0) $testPassed = true;

include('postTestProcedures.php');

?>