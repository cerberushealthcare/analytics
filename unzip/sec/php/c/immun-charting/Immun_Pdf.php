<?php
require_once 'php/pdf/PdfM.php';
require_once 'php/data/Html.php';
require_once 'php/data/file/client-pdf/_ClientPdfFile.php';
require_once 'php/c/immun-cds/ImmunCds.php';
//
class Immun_Pdf {
  //
  static function create(/*ImmunChart*/$chart, /*string*/$form, $until/*date*/ = null) {
    $cd = Immun_Cd::from($chart);
    switch ($form) {
      case 'KY':
        $cd = Immun_Cd::from($chart);
        return PdfM_ImmunForm_KY::from($chart, $cd, $until);
      default:
        global $login;
        return PDFM_ImmunGeneric::from($chart, $cd, $login->User->UserGroup->name);
    }
  }
}
//
/** Immunization Forms */
class PdfM_ImmunForm extends PdfM {
  //
  protected $chart;
  protected $cd; 
  protected $html;
  //
  static function from(/*ImmunChart*/$chart, /*Immun_Cd*/$cd = null, $until/*date*/ = null) {
    $me = static::create();
    $me->chart = $chart;
    $me->cd = $cd;
    $me->setHeader()->setBody($me->buildHtml($until));
    return $me;
  }
  public function download() {
    $filename = "IC_" . $this->chart->Client->clientId . ".pdf";
    parent::download($filename);
  }
}
/** Kentucky */
class PdfM_ImmunForm_KY extends PdfM_ImmunForm {
  //
  protected function buildHtml($until = null) {
    global $login;
    $c = $this->chart;
    $pox = Diag_Imm::fetchChickenPoxOrZoster($c->Client->clientId);
    $this->html = new Html();
    $this->div('cname', $c->Client->getFullName());
    $this->div('dob', date("m / d / Y", strtotime($c->Client->birth)));
    $this->div('parent', $c->Client->getParent());
    $this->div('addr', $c->Client->getHomeAddress());
    if ($until == null) {
      $next = $this->cd->getNextDue();
      if ($next) {
        $until = futureDate(14, 0, 0, $next);
      }
    }
    if ($until) {
      $this->div('until', $this->mdy($until, true));
    }
    if ($login)
      $this->div('pname', $login->User->UserGroup->name);
    if ($pox) 
      $this->div('pox', 'X&nbsp;');/*without &nbsp; the X gets dropped to next line!*/
    $this->imms('dtp', VCats::DTP, 5);
    $this->imms('hib', VCats::Hib, 4);
    $this->imms('pcv', VCats::PCV, 4);
    $this->imms('polio', VCats::Polio, 4);
    $this->imms('hepb', VCats::HepB, 3);
    $this->imms('hepba', VCats::HepB_Adult, 2);
    $this->imms('mmr', VCats::MMR, 2);
    $this->imms('var', VCats::Varicella, 2);
    $this->imms('tdap', VCats::Tdap, -1);
    $this->imms('td', VCats::Td, -1/*last one, not the first*/);
    $this->imms('mcv', VCats::MCV, 1);
    return $this->html->out();
  }
  protected function div($class, $text) {
    if ($text)
      $this->html->div_($class)->add($text)->_();
  }
  protected function imms($class, $vcat, $count = 1) {
    if ($count < 0) {
      for ($i = -1; $i >= $count; $i--) 
        $this->imm($class, $vcat, $i);
    } else {
      for ($i = 0; $i < $count; $i++) 
        $this->imm($class, $vcat, $i);
    }
  }
  protected function imm($class, $vcat, $index) {
    $imm = $this->chart->get($vcat, $index);
    if ($imm) 
      $this->div($class . (abs($index) + 1), $this->mdy($imm->dateGiven));
  }
  protected function mdy($date, $extra = false) {
    $ts = strtotime($date);
    $pad = $extra ? '&nbsp;&nbsp;&nbsp;' : '&nbsp;&nbsp;';
    return date('m', $ts) . '&nbsp;&nbsp;' . date('d', $ts) . $pad . date('y', $ts);
  }
  protected static function getPageStyle() {
    $url = MyEnv::$PDF_URL . 'img/forms/Immun-KY-88.png';
    return <<<eos
margin:0;
background:#ffffff url($url) no-repeat center center;
eos;
  }
  protected static function getStyle() {
    $css = <<<eos
BODY {color:#000099;}
DIV.cname  {position:absolute;left:153px;top:240px;}
DIV.dob    {position:absolute;left:643px;top:240px;}
DIV.parent {position:absolute;left:245px;top:288px;}
DIV.addr   {position:absolute;left:115px;top:322px;}
DIV.dtp1   {position:absolute;left:307px;top:436px;}
DIV.dtp2   {position:absolute;left:408px;top:436px;}
DIV.dtp3   {position:absolute;left:504px;top:436px;}
DIV.dtp4   {position:absolute;left:601px;top:436px;}
DIV.dtp5   {position:absolute;left:698px;top:436px;}
DIV.hib1   {position:absolute;left:310px;top:467px;}
DIV.hib2   {position:absolute;left:408px;top:467px;}
DIV.hib3   {position:absolute;left:504px;top:467px;}
DIV.hib4   {position:absolute;left:601px;top:467px;}
DIV.pcv1   {position:absolute;left:310px;top:497px;}
DIV.pcv2   {position:absolute;left:408px;top:497px;}
DIV.pcv3   {position:absolute;left:504px;top:497px;}
DIV.pcv4   {position:absolute;left:601px;top:497px;}
DIV.polio1 {position:absolute;left:310px;top:528px;}
DIV.polio2 {position:absolute;left:408px;top:528px;}
DIV.polio3 {position:absolute;left:504px;top:528px;}
DIV.polio4 {position:absolute;left:601px;top:528px;}
DIV.hepb1  {position:absolute;left:213px;top:559px;}
DIV.hepb2  {position:absolute;left:310px;top:559px;}
DIV.hepb3  {position:absolute;left:408px;top:559px;}
DIV.hepba1 {position:absolute;left:601px;top:559px;}
DIV.hepba2 {position:absolute;left:698px;top:559px;}
DIV.mmr1   {position:absolute;left:310px;top:590px;}
DIV.mmr2   {position:absolute;left:408px;top:590px;}
DIV.var1   {position:absolute;left:166px;top:621px;}
DIV.var2   {position:absolute;left:262px;top:621px;}
DIV.pox    {position:absolute;left:711px;top:621px;}
DIV.tdap2  {position:absolute;left:166px;top:652px;}
DIV.td2    {position:absolute;left:359px;top:652px;}
DIV.mcv1   {position:absolute;left:649px;top:652px;}
DIV.until  {position:absolute;left:347px;top:728px;}
DIV.pname  {position:absolute;left: 60px;top:870px;}
eos;
    return parent::getStyle($css);
  }
}
/** Generic */
class PdfM_ImmunGeneric extends ClientPdfFile {
  //
  static function from(/*ImmunChart*/$chart, /*Immun_Cd*/$cd, $ugname) {
    $me = static::create();
    $me->client = $chart->Client;
    $me->setHeader($chart->Client, 'Immunization Record');
    $me->setBody($chart, $cd, $ugname);
    return $me;
  }
  //
  public function setBody($chart, $cd, $ugname) {
    $html = new Html();
    $html->h2('Immunization Certificate');
    foreach ($chart->Rows as $row) {
      if (! empty($row->Immuns)) {
        $html->h3($row->cat)->table_()->tr_();
        foreach ($row->Immuns as $imm) 
          $html->td(formatDate($imm->dateGiven));
        for ($i = count($row->Immuns); $i < 7; $i++) 
          $html->td('&nbsp;');
        $html->_(2);
        $ic = $cd->get($row->cat);
        if ($ic->na)
          $html->span($ic->na);
      }
    }
    $html->br()->br()->br()->br()->br('_________________________________________________')->br($ugname);
    return parent::setBody($html->out());
  }
  public function download() {
    $filename = "IC_" . $this->client->clientId . ".pdf";
    parent::download($filename);
  }
  protected function getCss() {
    return <<<eos
TD {border:1px solid black;width:14%;}
H3 {margin-bottom:4;}
H2 {text-align:center;}
eos;
  }
}
class Pdf_ImmunSingle extends ClientPdfFile {
  //
  static function from(/*Client_C*/$client, /*Immun_C*/$imm) {
    global $login;
    $ugname = $login->User->UserGroup->name;
    $me = static::create();
    $me->client = $client;
    $me->setHeader($client, 'Immunization');
    $me->setBody($imm, $ugname);
    return $me;
  }
  //
  public function setBody($imm, $ugname) {
    $html = new Html();
    $html->h2('Immunization Given');
    $html->table_()
      ->tr_()->th('Date Given')->td(formatDate($imm->dateGiven))->_()
      ->tr_()->th('Name')->td($imm->name)->_()
      ->tr_()->th('Admin By')->td($imm->adminBy)->_()
      ->tr_()->th('Dose')->td($imm->dose)->_()
      ->tr_()->th('Comment')->td($imm->comment)->_(2);
    $html->br()->br()->br()->br()->br('_________________________________________________')->br($ugname);
    return parent::setBody($html->out());
  }
  protected function getCss() {
    return <<<eos
TD {border:1px solid black;width:75%}
TH {text-align:right;padding-right:4;}
H3 {margin-bottom:4;}
H2 {text-align:center;}
eos;
  }
}