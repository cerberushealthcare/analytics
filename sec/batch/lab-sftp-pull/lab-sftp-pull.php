<?php
set_include_path('../../');
require_once 'php/dao/_util.php';
require_once 'batch/_batch.php';
require_once 'php/cbat/ftp-puller/FtpPuller.php';
//
blog('Start lab-sftp.php');
FtpPuller::exec();
blog('Finish lab-sftp.php');