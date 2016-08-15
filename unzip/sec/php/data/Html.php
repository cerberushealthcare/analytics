<?php
/**
 * HTML Builder 
 * @author Warren Hornsby
 * 
 * $body = Html::create()
 *   ->h2("Patient")
 *   ->ul_()
 *      ->li("Name: $name")
 *      ->li("DOB: $dob")
 *    ->_()
 *    ->out();
 */
class Html {
  //
  static function create() {
    return new static();
  }
  //
  private $h;
  private $tags;
  private $suppress;  
  //
  public function __construct() {
    $this->h = array();
    $this->tags = array();
  }
  public function out() {
    if (count($this->tags))
      $this->_(count($this->tags));
    return implode('', $this->h);
  }
  //
  public function add($html) {
    if ($html !== null && ! $this->suppress)
      $this->h[] = $html;
    return $this;
  }
  public function _($count = 1) {
    for ($i = 0; $i < $count; $i++) {
      $name = array_pop($this->tags);
      $this->add("</$name>");
    }
    return $this;
  }
  public function p_() {
    return $this->tag_('P');
  }
  public function p($inner) {
    return $this->p_()->add($inner)->_();
  }
  public function nbsp() {
    return $this->add('&nbsp;');
  }
  public function div_($class = null) {
    return $this->tag_('DIV', static::asClass($class));
  }
  public function div($inner) {
    return $this->tag_add('DIV', $inner);
  }
  public function span($inner) {
    return $this->tag_add('SPAN', $inner);
  }
  public function h1($inner) {
    return $this->tag_add('H1', $inner);
  }
  public function h2($inner) {
    return $this->tag_add('H2', $inner);
  }
  public function h3($inner) {
    return $this->tag_add('H3', $inner);
  }
  public function b($text) {
    return $this->tag_add('B', $text);
  }
  public function a($url, $text = null) {
    return $this->tag_('A', array('href'=>$url))->add($text ?: $url)->_();
  }
  public function img($attrs) {
    if (is_string($attrs))
      $attrs = array('src'=>$attrs);
    return $this->tag_('IMG', $attrs)->_();
  }
  public function br($beforeText = null) {
    return $this->add($beforeText)->tag('BR');
  }
  public function hr() {
    return $this->tag('HR');
  }
  public function ul_($class = null) {
    return $this->tag_('UL', static::asClass($class));
  }
  public function ul($inners) {
    return $this->ul_()->li($inners)->_();
  }
  public function li_() {
    return $this->tag_('LI');
  }
  public function li($inner) {
    return $this->tag_add('LI', $inner);
  }
  public function table_($attrs = null) {
    return $this->tag_('TABLE', $attrs);
  }
  public function tr_($attrs = null) {
    return $this->tag_('TR', $attrs);
  }
  public function tr($inners) {
    return $this->tr_()->td($inners)->_();
  }
  public function td($inner) {  // $inner may be array
    return $this->tag_add('TD', $inner);
  }
  public function th($inner) {
    return $this->tag_add('TH', $inner);
  }
  public function if_($cond) {  // not nestable
    $this->suppress = ! $cond;
    return $this; 
  }
  public function _if() {
    $this->suppress = false;
    return $this;
  } 
  //
  protected function tag_($name, $attrs = null) {
    $this->add("<$name");
    if (! empty($attrs)) {
      if (is_string($attrs)) {
        $this->add(" $attrs");
      } else if (is_array($attrs)) { 
        foreach ($attrs as $attr => $value) 
          $this->add(" $attr=\"$value\"");
      }
    }
    $this->add(">");
    $this->tags[] = $name;
    return $this;
  }
  protected function tag($name) {
    return $this->add("<$name />");
  }
  protected function tag_add($name, $inner) {
    if (is_array($inner)) {
      foreach ($inner as $i) 
        $this->tag_add($name, $i);
    } else { 
      $this->tag_($name)->add($inner)->_();
    }
    return $this;
  }
  //
  protected static function asAttr($name, $value) {
    if ($value)
      return array($name => $value);
  }
  protected static function asClass($class) {
    return array('class' => $class);
  }
}