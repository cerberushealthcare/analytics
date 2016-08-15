<?php
require_once 'php/data/_BasicRec.php';
//
/** 
 * Hepatitus B */
class IS_HepB extends ImmunSched {
  //
  static $DS_CLASS = 'DS_HepB';
}
class DS_HepB extends DoseSched {
  //
  static function asRoutines() {
    return array(static::from()->doses(
      Dose::from(Tv::birth),
      Dose::from(Tv::m(1), Tv::m(2)),
      Dose::from(Tv::m(6), Tv::m(18))));
  }
  static function asCatchups() {
    return array(
      static::asCatchupRecombivax(),
      static::asCatchup()); 
  }
  static function asCatchup() {
    return static::from()->doses(
      Dose::from(Tv::birth),
      Dose::from()->int(Tv::w(4)),
      Dose::from(Tv::w(24))->int(Tv::w(16), 1)->int(Tv::w(8), 2));
  }
  static function asCatchupRecombivax() {
    return static::from('Recombivax')->doses(
      Dose::from(Tv::y(11)),
      Dose::from()->int(Tv::m(4)));
  }
}
/** 
 * Rotavirus */
class IS_RV extends ImmunSched {
  //
  static $DS_CLASS = 'DS_RV';
}
class DS_RV extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asRoutineRV1(),
      static::asRoutineRV5());
  }
  static function asCatchups() {
    return array(
      static::asCatchupRV1(),
      static::asCatchupRV5());
  }
  protected static function asRoutineRV1() {
    return static::from('Rotarix')->doses(
      Dose::from(Tv::m(2), Tv::m(4)),
      Dose::from(Tv::m(4), Tv::m(8)));
  }
  protected static function asRoutineRV5() {
    return static::from('RotaTeq')->doses(
      Dose::from(Tv::m(2), Tv::m(4)),
      Dose::from(Tv::m(4), Tv::m(6)),
      Dose::from(Tv::m(6), Tv::m(8)));
  }
  protected static function asCatchupRV1() {
    return static::from('Rotarix')->doses(
      Dose::from(Tv::w(6), Tv::w(14, 6)),
      Dose::from(Tv::m(4), Tv::m(8))->int(Tv::w(4)));
  }
  protected static function asCatchupRV5() {
    return static::from('RotaTeq')->doses(
      Dose::from(Tv::w(6), Tv::w(14, 6)),
      Dose::from(Tv::m(4), Tv::m(6))->int(Tv::w(4)),
      Dose::from(Tv::m(6), Tv::m(8))->int(Tv::w(4)));
  }
}
/** 
 * Diptheria, tetanus, pertussis */
class IS_DTaP extends ImmunSched {
  //
  static $DS_CLASS = 'DS_DTaP';
}
class DS_DTap extends DoseSched {
  //
  static function asRoutines() {
    return array(static::from()->doses(
      Dose::from(Tv::m(2), Tv::m(4)),
      Dose::from(Tv::m(4), Tv::m(6)),
      Dose::from(Tv::m(6), Tv::m(9)),
      Dose::from(Tv::m(15), Tv::m(18)),
      Dose::from(Tv::y(4), Tv::m(6))));
  }
  static function asCatchups() {
    return array(static::from()->doses(
      Dose::from(Tv::w(6), Tv::y(6)),
      Dose::from(Tv::w(6), Tv::y(6))->int(Tv::w(4)),
      Dose::from(Tv::w(6), Tv::y(6))->int(Tv::w(4)),
      Dose::from(Tv::w(6), Tv::y(4))->int(Tv::m(6)),
      Dose::from(Tv::y(4), Tv::y(6))->int(Tv::m(6))));
  }
}
/** 
 * Tetanus, diphtheria, pertussis */
class IS_TdaP extends ImmunSched {
  //
  static $DS_CLASS = 'DS_Tdap';
}
class DS_Tdap extends DoseSched {
  //
  static function asRoutines() {
    return array(static::from()->doses(
      Dose::from(Tv::y(11), Tv::y(12))));
  }
  static function asCatchups() {
    return array(static::from()->doses(
      Dose::from(Tv::w(6), Tv::y(6)),
      Dose::from(Tv::w(6), Tv::y(6))->int(Tv::w(4)),
      Dose::from(Tv::w(6), Tv::y(6))->int(Tv::w(4)),
      Dose::from(Tv::w(6), Tv::y(4))->int(Tv::m(6)),
      Dose::from(Tv::y(4), Tv::y(6))->int(Tv::m(6))));
  }
}