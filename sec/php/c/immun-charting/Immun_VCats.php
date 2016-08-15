<?php
class VCats { 
  //
  public /*string[]*/$array;
  //
	/** scheduled */
  const DTP_All = 'DTaP/Tdap';
  const HepB = 'Hepatitis B';
  const MMR = 'MMR';
  const Polio = 'Polio'; 
  const Hib = 'Hib';
  const Varicella = 'Varicella';
  const HepA = 'Hepatitis A';
  const PCV = 'Pneumococcal';
  const MCV = 'Meningococcal';
  const RV = 'Rotavirus';
  const HPV = 'HPV';
	/** other charting combinations */
  const Flu = 'Influenza';
  const DTP = 'DTap,DTP,DT';
  const TdapTd = 'Tdap,Td';
  const Td = 'Td';
  const Tdap = 'Tdap';
  const HepB_PedAdol = 'HepB(Ped/Adol)';
  const HepB_Adult = 'HepB(Adult)';
  //
  static $MAP = array(
		/** scheduled */
    self::HepB => array(VTypes::HepB, VTypes::HepB_Adult),
    self::RV => array(VTypes::RV),
    self::DTP_All => array(VTypes::DTaP, VTypes::DTP, VTypes::DT, VTypes::Td, VTypes::Tdap),
    self::Hib => array(VTypes::Hib),
    self::PCV => array(VTypes::PCV),
    self::Polio => array(VTypes::IPV, VTypes::OPV),
    self::MMR => array(VTypes::MMR),
    self::Varicella => array(VTypes::Varicella),
    self::HepA => array(VTypes::HepA),
    self::HPV => array(VTypes::HPV),
    self::MCV => array(VTypes::MCV),
    /** other charting combinations */
    self::Flu => array(VTypes::Flu),
    self::DTP => array(VTypes::DTaP, VTypes::DTP, VTypes::DT),
    self::TdapTd => array(VTypes::Td, VTypes::Tdap),
    self::Td => array(VTypes::Td),
    self::Tdap => array(VTypes::Tdap),
    self::HepB_PedAdol => array(VTypes::HepB),
    self::HepB_Adult => array(VTypes::HepB_Adult));
  //
  static $SCHEDULED = array(self::HepB, self::RV, self::DTP_All, self::Hib, self::PCV, self::Polio, self::MMR, self::Varicella, self::HepA, self::HPV, self::MCV);
  //
  static function from(/*VTypes*/$types) {
    $me = new static();
    $me->array = array();
    foreach (static::$MAP as $cat => $ctypes) 
      $me->addIfApplies($types, $cat, $ctypes);
    return $me;
  }
  static function getAll() {
    return array_keys(static::$MAP);
  }
  static function getScheduled() {
    return static::$SCHEDULED;
  }
  //
  protected function addIfApplies($types, $cat, $ctypes) {
    foreach ($types->array as $type) { 
      if (in_array($type, $ctypes)) {
        $this->array[] = $cat;
        return;
      }
    }
  }
}
class VTypes {
  //
  public /*string[]*/$array;
  //
	/** vtypes */
  const DTaP = 'DTaP';
  const DTP = 'DTP';
  const DT = 'DT';
  const Td = 'Td';
  const Tdap = 'Tdap';
  const HepB = 'HepB';
  const HepB_Adult = 'HepB(Adult)';
  const MMR = 'MMR';
  const IPV = 'IPV';
  const OPV = 'OPV';
  const Hib = 'Hib';
  const Varicella = 'Varicella';
  const HepA = 'HepA';
  const PCV = 'PCV';
  const MCV = 'MCV';
  const RV = 'RV';
  const HPV = 'HPV';
  const Flu = 'Flu';
  const MMRV = 'MMRV';
  //
  static function from(/*Immun_C*/$imm) {
    $me = new static();
    $me->array = array();
    $me->addDtp($imm);
    $me->addHepB($imm);
    $me->addHepA($imm);
    $me->addVaricella($imm);
    $me->addPolio($imm);
    $me->addMMR($imm);
    $me->addIfContains($imm, static::Hib);
    $me->addIfContains($imm, static::PCV, 'Pneumo');
    $me->addIfContains($imm, static::MCV, 'Mening');
    $me->addIfContains($imm, static::RV, 'Rotavirus');
    $me->addIfContains($imm, static::HPV);
    $me->addIfContains($imm, static::Flu);
    return $me;
  }
  //
  protected function addMMR($imm) {
    if ($this->addIfContains($imm, static::MMR))
      return;
    if ($this->addIfEquals($imm, static::MMR, 'MR'))
      return;
    if ($this->addIfContains($imm, static::MMR, 'mumps'))
      return;
    if ($this->addIfContains($imm, static::MMR, 'measles'))
      return;    
  }
  protected function addPolio($imm) {
    if ($this->addIfContains($imm, static::IPV))
      return;
    if ($this->addIfEquals($imm, static::OPV))
      return;
    if ($this->addIfContains($imm, static::IPV, 'polio'))
      return;
  }
  protected function addDtp($imm) {
    if ($this->addIfContains($imm, static::DTaP))
      return;
    if ($this->addIfContains($imm, static::DTP))
      return;
    if ($this->addIfEquals($imm, static::DT))
      return;
    if ($this->addIfContains($imm, static::DT, 'DT '))
      return;
    if ($this->addIfEquals($imm, static::Td))
      return;
    if ($this->addIfContains($imm, static::Td, 'Td '))
      return;
    if ($this->addIfEquals($imm, static::Tdap))
      return;
    if ($this->addIfEquals($imm, static::Tdap, 'pertussis'))
      return;
  }
  protected function addVaricella($imm) {
    if ($this->addIfContains($imm, static::Varicella))
      return;
    if ($this->addIfEquals($imm, static::Varicella, 'MMRV'))
      return;
  }
  protected function addHepB($imm) {
    if ($imm->nameHas(static::HepB) || $imm->nameHas('Hep B') || $imm->nameHas('Hepatitis B')) 
      $this->add(($imm->isAdult() ? static::HepB_Adult : static::HepB));
  }
  protected function addHepA($imm) {
    if ($imm->nameHas('Hep A') || $imm->nameHas('Hepatitis A')) 
      $this->add(static::HepA);
  }
  protected function addIfEquals($imm, $vtype, $value = null) {
    return $this->add($vtype, $imm->nameIs($value ?: $vtype));
  }
  protected function addIfContains($imm, $vtype, $value = null) {
    return $this->add($vtype, $imm->nameHas($value ?: $vtype));
  }
  protected function add($vtype, $cond = true) {
    if ($cond)
      $this->array[] = $vtype;
    return $cond;
  }
}
class VNames {
  //
  const Recombivax_Adult = 'Recombivax(Adult)';
  const RV1 = 'RV1';
  const Hib_PRP_OMP = 'Hib PRP-OMP';
  const PCV7 = 'PCV7';
  const OPV_IPV = 'OPV/IPV';
  //
  static function applies(/*Immun_C*/$imm, $vname) {
    switch ($vname) {
      case static::Recombivax_Adult:
        return $imm->tradeNameHas('Recombivax') && $imm->isAdult();
      case static::Hib_PRP_OMP:
        return $imm->tradeNameHas('PedvaxHib') || $imm->tradeNameHas('Comvax');
      case static::RV1:
        return $imm->tradeNameHas('Rotarix') || $imm->tradeNameHas('RV1');
      case static::OPV_IPV:
        return $imm->nameIs('IPV') || $imm->nameIs('OPV');
      default:
        return $imm->tradeNameHas($vname); 
    }
  }
}
