<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/cryptastic.php';
require_once 'php/c/user-profile/UserProfile.php';
require_once 'php/data/rec/sql/_BillingRecs.php';
//
LoginSession::verify_forServer();
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php 
switch ($_GET['t']) {
  case '1':
    $name = <<<eos
12345678123456781234567812345678123456781234567812345678123S
eos;
    p_r($name, strlen($name));
    $a = MyCrypt::encrypt($name, 'jjW1p3!afs');
    p_r($a, strlen($a));
    $name = '5000123488886543';
    p_r($name, strlen($name));
    $a = MyCrypt::encrypt($name, 'jjW1p3!afs');
    p_r($a, strlen($a));
    $name = '509 Macon Avenue Louisville KY 40502';
    p_r($name, strlen($name));
    $b = MyCrypt::encrypt($name, 'jjW1p3!afs');
    p_r($b, strlen($b));
    $name = 'This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable.';
    p_r($name, strlen($name));
    $c = MyCrypt::encrypt($name, 'jjW1p3!afs');
    p_r($c, strlen($c));
    $name = 'This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable. This is some fairly long text that we will use to simulate a comment that may be put in for some kind of data element which should be obfuscated in a manner that is completely indecipherable.';
    p_r($name, strlen($name));
    $d = MyCrypt::encrypt($name, 'jjW1p3!afs');
    p_r($d, strlen($d));
    $name = MyCrypt::decrypt($a, 'jjW1p3!afs');
    p_r($name, 'a');
    $name = MyCrypt::decrypt($b, 'jjW1p3!afs');
    p_r($name, 'b');
    $name = MyCrypt::decrypt($c, 'jjW1p3!afs');
    p_r($name, 'c');
    $name = MyCrypt::decrypt($d, 'jjW1p3!afs');
    p_r($name, 'd');
    exit;
  case '2':
    $p = UserProfile::getMine();
    p_r($p);
    exit;
  case '3':
    $p = UserProfile::getMine();
    $bs = $p->Bill;
    p_r($bs);
    $bs->addr1 = '509 Macon Ave';
    p_r($bs);
    $p = UserProfile::saveBill($bs);
    p_r($bs);
    exit;
  case '4':
    $c = new cryptastic();
    $pw = 'jjW1p3!afs';
    $salt = 'lcd solutions';
    $x = $c->pbkdf2($pw, $salt, 1, 32);
    p_r('>' . $x . '<');
    exit;
  case '5':
    p_r(MyCrypt::encrypt('Anne', 'fred'));
    p_r(MyCrypt::encrypt('Anne', 'fred'));
    exit;
  case '6':
    $rec = new stdClass();
    $rec->name = 'Warren Hornsby';
    MyCrypt_Sql::encrypt($rec, 'name');
    p_r($rec);
    MyCrypt_Sql::decrypt($rec, 'name');
    p_r($rec);
    exit;
  case '7':
    p_r(MyCrypt::decrypt('F4Gi2tJ8S37pEQO+o6fRxMkoQDmi+FZC9wSbiBCwGTW8zKOu', 'jjW1p3!afs'));
    exit;
  case '10':
    $rec = BillStatus::fetch(1);
    p_r($rec);
    $rec = BillStatus::fetch(2);
    p_r($rec);
    exit;
    
}
?>
</html>