<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/_SqlRec.php';
//
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php 
class Mom extends SqlRec {
  static $SQL_TABLE = 'parent';
  //
  public $momId;
  public $name;
  public $alphaId;
  public $betaId;
  public $zetaId;
  //
  static function asCriteria($momId) {
    $c = new static($momId);
    $c->Alpha = Alpha::asRequiredJoin();
    $c->Beta = Beta::asRequiredJoin();
    $c->Zeta = Zeta::asRequiredJoin();
    return $c;
  }
}
class Alpha extends SqlRec {
  static $SQL_TABLE = 'alpha';
  //
  public $alphaId;
  public $atext;
}
class Beta extends SqlRec {
  static $SQL_TABLE = 'beta';
  //
  public $betaId;
  public $btext;
  public $gammaId;
  public $epsilonId;
  //
  static function asJoinCriteria() {
    $c = new static();
    $c->Gamma = Gamma::asRequiredJoin(); 
    $c->Epsilon = Epsilon::asRequiredJoin(); 
    return $c;
  }
}
class Gamma extends SqlRec {
  static $SQL_TABLE = 'gamma';
  //
  public $gammaId;
  public $gtext;
  public $deltaId;
  //
  static function asJoinCriteria() {
    $c = new static();
    $c->Delta = Delta::asRequiredJoin(); 
    return $c;
  }
}
class Delta extends SqlRec {
  static $SQL_TABLE = 'delta';
  //
  public $deltaId;
  public $dtext;
}
class Epsilon extends SqlRec {
  static $SQL_TABLE = 'epsilon';
  //
  public $epsilonId;
  public $etext;
}
class Zeta extends SqlRec {
  static $SQL_TABLE = 'zeta';
  //
  public $zetaId;
  public $ztext;
}
//
switch ($_GET['t']) {
  case '1':
    $c = Mom::asCriteria(14);
    p_r(Logger::formatSql($c->getSqlSelect()));
    exit;
  case '2':
    $a = array();
    $a['S1'] = 'JOIN (alpha T1';
    $a['E1'] = ') ON T0.`alpha_id`=T1.`alpha_id`';
    $a['S2'] = 'JOIN beta T2 (';
    $a['E2'] = ') ON T0.`beta_id`=T2.`beta_id`';
    p_r($a);
    $i = 3;
    $joinsIx = 0;
    $b = array();
    $b['S3'] = 'JOIN gamma T3 (';
    $b['E3'] = ') ON T2.`gamma_id`=T3.`gamma_id`';
    p_r($b);
    $a = array_insert($a, $b, key_offset('E' . $joinsIx, $a));
    p_r($a);
}
?>
</html>