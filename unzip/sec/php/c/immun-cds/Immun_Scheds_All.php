<?php
require_once 'php/c/immun-charting/Immun_VCats.php';
//
/** Hepatitus B */
class IS_HepB extends ImmunSched {
  //
  static $VCAT = VCats::HepB;
  static $DS_CLASS = 'DS_HepB';
}
class DS_HepB extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_HepB());
  }
  static function asCatchups() {
    return array(
      static::asC_HepB(), 
      static::asC_Recombivax());
  }
  protected static function asR_HepB() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(0, 1)),
      Dose::from(Ages::m(1, 2)),
      Dose::from(Ages::m(6, 18)));
  }
  protected static function asC_Recombivax() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::y(11, 15))->name(VNames::Recombivax_Adult),
      Dose::from(Ages::y(11, 15), Int::m(4))->name(VNames::Recombivax_Adult));  
  }
  protected static function asC_HepB() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(0, 18)),
      Dose::from(Ages::wy(0, 18), Int::w(4)),
      Dose::from(Ages::wy(24, 18))->ints(Int::w(16)->from(1), Int::w(8)->from(2)));
  }
}
/** Rotavirus */
class IS_RV extends ImmunSched {
  //
  static $VCAT = VCats::RV;
  static $DS_CLASS = 'DS_RV';
}
class DS_RV extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_Rotarix(),
      static::asR_RV());
  }
  static function asCatchups() {
    return array(
      static::asC_Rotarix(),
      static::asC_RV());
  }
  protected static function asR_Rotarix() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(2, 4))->name(VNames::RV1),
      Dose::from(Ages::m(4, 8))->name(VNames::RV1));
  }
  protected static function asR_RV() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(2, 4)),  
      Dose::from(Ages::m(4, 6)),
      Dose::from(Ages::m(6, 8)));
  }
  protected static function asC_Rotarix() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::w(6, 15))->name(VNames::RV1),
      Dose::from(Ages::m(4, 8), Int::w(4))->name(VNames::RV1));
  }
  protected static function asC_RV() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::w(6, 15)),
      Dose::from(Ages::m(4, 6), Int::w(4)),
      Dose::from(Ages::m(6, 8), Int::w(4)));
  }
}
/** DTap/Tdap */
class IS_DTP extends ImmunSched {
  //
  static $VCAT = VCats::DTP_All;
  static $DS_CLASS = 'DS_DTP';
}
class DS_DTP extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_DTP1(),
      static::asR_DTP2());
  }
  static function asCatchups() {
    return array(
      static::asC_DTP1(),
      static::asC_DTP2(),
      static::asC_DTP3(),
      static::asC_DTP4(),
      static::asC_DTP5(),
      static::asC_DTP6(),
      static::asC_DTP7(),
      static::asC_DTP8(),
      static::asC_DTP9(),
      static::asC_DTP10());
  }
  protected static function asR_DTP1() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(2, 4))->type(VTypes::DTaP),
      Dose::from(Ages::m(4, 6))->type(VTypes::DTaP),
      Dose::from(Ages::m(6, 9))->type(VTypes::DTaP),
      Dose::from(Ages::m(15, 18))->type(VTypes::DTaP),
      Dose::from(Ages::y(4, 6))->type(VTypes::DTaP),
      Dose::from(Ages::y(11, 12))->type(VTypes::Tdap));
  }
  protected static function asR_DTP2() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(2, 4))->type(VTypes::DTaP),
      Dose::from(Ages::m(4, 6))->type(VTypes::DTaP),
      Dose::from(Ages::m(6, 9))->type(VTypes::DTaP),
      Dose::from(Ages::m(15, 18))->type(VTypes::DTaP),
      Dose::from(Ages::y(4, 6))->type(VTypes::DTaP),
      Dose::from(Ages::y(7, 10))->type(VTypes::DTaP)/*inadvertent DTaP*/,
      Dose::from(Ages::y(11, 12))->type(VTypes::Tdap)->optional());
  }
  protected static function asC_DTP1() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::wy(6, 7), Int::m(6)),
      Dose::from(Ages::y(4, 7), Int::m(6)));
  } 
  protected static function asC_DTP2() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::wy(6, 4), Int::m(6)),
      Dose::from(Ages::y(7, 18)));
  } 
  protected static function asC_DTP3() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 7)),
      Dose::from(Ages::wy(6, 7), Int::w(4)),
      Dose::from(Ages::wy(6, 7), Int::w(4)),
      Dose::from(Ages::wy(6, 7), Int::m(6)));
  } 
  protected static function asC_DTP4() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 7)),
      Dose::from(Ages::wy(6, 7), Int::w(4)),
      Dose::from(Ages::wy(6, 7), Int::w(4)),
      Dose::from(Ages::y(7, 18)));
  } 
  protected static function asC_DTP5() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wm(6, 12)),
      Dose::from(Ages::wy(6, 7), Int::w(4)),
      Dose::from(Ages::y(7, 18)),
      Dose::from(Ages::y(7, 18), Int::w(4)));
  } 
  protected static function asC_DTP6() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::my(12, 7)),
      Dose::from(Ages::my(12, 7), Int::w(4)),
      Dose::from(Ages::y(7, 18)),
      Dose::from(Ages::y(7, 18), Int::m(6)));
  } 
  protected static function asC_DTP7() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 7)),
      Dose::from(Ages::y(7, 18)),
      Dose::from(Ages::y(7, 18), Int::w(4)),
      Dose::from(Ages::y(7, 18), Int::w(4)));
  } 
  protected static function asC_DTP8() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::my(12, 7)),
      Dose::from(Ages::y(7, 18)),
      Dose::from(Ages::y(7, 18), Int::w(4)),
      Dose::from(Ages::y(7, 18), Int::m(6)));
  } 
  protected static function asC_DTP9() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::y(7, 11)),
      Dose::from(Ages::y(11, 18), Int::w(4)),
      Dose::from(Ages::y(11, 18), Int::m(6)));
  } 
  protected static function asC_DTP10() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::y(11, 18)));
  } 
}
/** Hib */
class IS_Hib extends ImmunSched {
  //
  static $VCAT = VCats::Hib;
  static $DS_CLASS = 'DS_Hib';
}
class DS_Hib extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_PRP_OMP(),
      static::asR_Hib());
  }
  static function asCatchups() {
    return array(
      static::asC_Hib1(),
      static::asC_Hib2(),
      static::asC_Hib3(),
      static::asC_Hib4(),
      static::asC_Hib5(),
      static::asC_Hib6(),
      static::asC_Hib7(),
      static::asC_Hib8()); 
  }
  protected static function asR_PRP_OMP() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(2, 4))->name(VNames::Hib_PRP_OMP),
      Dose::from(Ages::m(4, 6))->name(VNames::Hib_PRP_OMP),
      Dose::from(Ages::m(12, 15)));
  }
  protected static function asR_Hib() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(2, 4)),
      Dose::from(Ages::m(4, 6)),
      Dose::from(Ages::m(6, 12)),
      Dose::from(Ages::m(12, 15)));
  }
  protected static function asC_Hib1() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wm(6, 12))->name(VNames::Hib_PRP_OMP),
      Dose::from(Ages::wm(6, 12), Int::w(4))->name(VNames::Hib_PRP_OMP),
      Dose::from(Ages::m(12, 15), Int::w(8)));
  }
  protected static function asC_Hib2() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wm(6, 7)),
      Dose::from(Ages::wm(6, 12), Int::w(4)),
      Dose::from(Ages::wm(6, 12), Int::w(4)),
      Dose::from(Ages::m(12, 60), Int::w(8)));
  }
  protected static function asC_Hib3() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wm(6, 7)),
      Dose::from(Ages::wm(6, 15)/*overlap intentional*/, Int::w(4)),
      Dose::from(Ages::my(12, 5), Int::w(8))); 
  }
  protected static function asC_Hib4() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wm(6, 7)),
      Dose::from(Ages::wm(6, 15), Int::w(4)),
      Dose::from(Ages::my(12, 5), Int::w(8)));
  }
  protected static function asC_Hib5() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wm(6, 7)),
      Dose::from(Ages::my(15, 5), Int::w(4)));
  }
  protected static function asC_Hib6() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(7, 12)),
      Dose::from(Ages::m(7, 15), Int::w(4)),
      Dose::from(Ages::m(12, 15), Int::w(8)));
  }
  protected static function asC_Hib7() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(7, 15)),
      Dose::from(Ages::my(12, 5), Int::w(8)));
  }
  protected static function asC_Hib8() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::my(15, 5)));
  }
}
/** PCV */
class IS_PCV extends ImmunSched {
  //
  static $VCAT = VCats::PCV;
  static $DS_CLASS = 'DS_PCV';
}
class DS_PCV extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_PCV7(),
      static::asR_PCV());
  }
  static function asCatchups() {
    return array(
      static::asC_PCV1(),
      static::asC_PCV2(),
      static::asC_PCV3(),
      static::asC_PCV4(),
      static::asC_PCV5()); 
  }
  protected static function asR_PCV7() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(2, 4))->name(VNames::PCV7),
      Dose::from(Ages::m(4, 6))->name(VNames::PCV7),
      Dose::from(Ages::m(6, 9))->name(VNames::PCV7),
      Dose::from(Ages::m(12, 15))->name(VNames::PCV7),
      Dose::from(Ages::my(14,5)));
  }
  protected static function asR_PCV() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(2, 4)),
      Dose::from(Ages::m(4, 6)),
      Dose::from(Ages::m(6, 12)),
      Dose::from(Ages::m(12, 15)));
  }
  protected static function asC_PCV1() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wm(6, 12)),
      Dose::from(Ages::wm(6, 12), Int::w(4)),
      Dose::from(Ages::wm(6, 12), Int::w(4)),
      Dose::from(Ages::my(12, 5), Int::w(8)));
  }
  protected static function asC_PCV2() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wm(6, 12)),
      Dose::from(Ages::wm(6, 24), Int::w(4)),
      Dose::from(Ages::wy(6, 5), Int::w(8)));
  }
  protected static function asC_PCV3() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wm(6, 12)),
      Dose::from(Ages::my(24, 5), Int::w(8)));
  }
  protected static function asC_PCV4() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(12, 24)),
      Dose::from(Ages::my(12, 5), Int::w(8)));
  }
  protected static function asC_PCV5() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::my(24, 5)));
  }
}
/** Varicella */
class IS_Varicella extends ImmunSched {
  //
  static $VCAT = VCats::Varicella;
  static $DS_CLASS = 'DS_Varicella';
  //
  public function isNotApplicable($rec) {
    $diag = Diag_Imm::fetchChickenPoxOrZoster($rec->client->clientId);
    if ($diag) 
      return 'Diagnosed with ' . $diag->text . ' on ' . formatDate($diag->date);
  }
}
class DS_Varicella extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_Varicella());
  }
  static function asCatchups() {
    return array(
      static::asC_Varicella()); 
  }
  protected static function asR_Varicella() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(12, 15)),
      Dose::from(Ages::y(4, 6)));
  }
  protected static function asC_Varicella() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::my(12, 18)),
      Dose::from(Ages::y(4, 18), Int::m(3)));
  }
}
class Diag_Imm extends SqlRec implements ReadOnly {
  //
  public $dataDiagnosesId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
  public $text;
  public $icd;
  public $active;
  public $dateUpdated;
  public $dateClosed;
  public $status;
  //
  const STATUS_RULED_OUT = '11';
  const ICD_CHICKENPOX = '052';
  const ICD_ZOSTER = '053';
  //
  public function getSqlTable() {
    return 'data_diagnoses';
  }
  //
  static function fetchChickenPoxOrZoster($cid) {
    $c = static::asCrit_ChickenPoxOrZoster($cid);
    $rec = static::fetchOneBy($c);
    return $rec;
  }
  protected static function asCrit_ChickenPoxOrZoster($cid) {
    $c = static::asCrit($cid);
    $c->icd = CriteriaValues::_or(
      CriteriaValue::startsWith(static::ICD_CHICKENPOX),
      CriteriaValue::startsWith(static::ICD_ZOSTER));
    return $c;
  }
  protected static function asCrit($cid) {
    $c = new static();
    $c->clientId = $cid;
    $c->sessionId = CriteriaValue::isNull();
    $c->status = CriteriaValue::notEquals(static::STATUS_RULED_OUT);
    return $c;
  }
}
/** Hepatitus A */
class IS_HepA extends ImmunSched {
  //
  static $VCAT = VCats::HepA;
  static $DS_CLASS = 'DS_HepA';
}
class DS_HepA extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_HepA());
  }
  static function asCatchups() {
    return array(
      static::asC_HepA()); 
  }
  protected static function asR_HepA() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(12, 24)),
      Dose::from(Ages::my(12, 18), Int::m(6)));
  }
  protected static function asC_HepA() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::my(12, 18)),
      Dose::from(Ages::my(12, 18), Int::m(6)));
  }
}
/** Meningococcal */
class IS_MCV extends ImmunSched {
  //
  static $VCAT = VCats::MCV;
  static $DS_CLASS = 'DS_MCV';
}
class DS_MCV extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_MCV());
  }
  static function asCatchups() {
    return array(
      static::asC_MCV1(),
      static::asC_MCV2()); 
  }
  protected static function asR_MCV() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::y(11, 12)),
      Dose::from(Ages::y(16, 18)));
  }
  protected static function asC_MCV1() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::y(13, 16)),
      Dose::from(Ages::y(13, 18), Int::w(8)));
  }
  protected static function asC_MCV2() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::y(16, 18)));
  }
}
/** Measles, mumps, rubella */
class IS_MMR extends ImmunSched {
  //
  static $VCAT = VCats::MMR;
  static $DS_CLASS = 'DS_MMR';
}
class DS_MMR extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_MMR());
  }
  static function asCatchups() {
    return array(
      static::asC_MMR()); 
  }
  protected static function asR_MMR() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(12, 15)),
      Dose::from(Ages::y(4, 6)));
  }
  protected static function asC_MMR() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::my(12, 18)),
      Dose::from(Ages::y(4, 18), Int::w(4)));
  }
}
/** Polio */
class IS_Polio extends ImmunSched {
  //
  static $VCAT = VCats::Polio;
  static $DS_CLASS = 'DS_Polio';
}
class DS_Polio extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_Polio());
  }
  static function asCatchups() {
    return array(
      static::asC_Polio1(),
      static::asC_Polio2(),
      static::asC_Polio3(),
      static::asC_Polio4(),
      static::asC_Polio5()); 
  }
  protected static function asR_Polio() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::m(2, 4)),
      Dose::from(Ages::m(4, 6)),
      Dose::from(Ages::m(6, 18)),
      Dose::from(Ages::y(4, 6)));
  }
  protected static function asC_Polio1() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 18))->name(VNames::OPV_IPV),
      Dose::from(Ages::wy(6, 18), Int::w(4))->name(VNames::OPV_IPV),
      Dose::from(Ages::wy(6, 18), Int::w(4))->name(VNames::OPV_IPV),
      Dose::from(Ages::wy(6, 18), Int::m(6))->name(VNames::OPV_IPV));
  }
  protected static function asC_Polio2() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::y(4, 6), Int::m(6)));
  }
  protected static function asC_Polio3() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::y(4, 6), Int::m(6)));
  }
  protected static function asC_Polio4() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::wy(6, 4), Int::w(4)),
      Dose::from(Ages::y(4, 6), Int::m(6)));
  }
  protected static function asC_Polio5() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::wy(6, 4)),
      Dose::from(Ages::y(4, 6), Int::m(6)));
  }
}
/** Human papillomavirus */
class IS_HPV extends ImmunSched {
  //
  static $VCAT = VCats::HPV;
  static $DS_CLASS = 'DS_HPV';
}
class DS_HPV extends DoseSched {
  //
  static function asRoutines() {
    return array(
      static::asR_HPV());
  }
  static function asCatchups() {
    return array(
      static::asC_HPV()); 
  }
  protected static function asR_HPV() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::y(11, 13)),
      Dose::from(Ages::y(11, 14), Int::m(1)),
      Dose::from(Ages::y(11, 14), Int::m(6)->from(1)));
  }
  protected static function asC_HPV() {
    return static::create(__METHOD__)->doses(
      Dose::from(Ages::y(13, 18)),
      Dose::from(Ages::y(13, 18), Int::m(1)),
      Dose::from(Ages::y(13, 18), Int::m(6)->from(1)));
  }
}
