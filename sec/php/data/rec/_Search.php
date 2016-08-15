<?php
require_once 'php/data/rec/_Rec.php';
/**
 * SearchText  
 */
class SearchText extends Rec {
  public $text;     // 'WORDS TO SEARCH FOR'
  public $expr;     // 'WORDS|SEARCH'
  public $words;    // ['WORDS','SEARCH']
  public $pattern;  // '/WORDS|SEARCH/i'
  public $ct;       // 2
  // 
  /**
   * @param string $text 'words to search for'
   */
  public function __construct($text) {
    $this->text = SearchText::trimUpper($text);
    $words = $this->split($text);
    $this->words = $words;
    $this->expr = implode('|', $words);
    $this->pattern = "/$this->expr/i";
    $this->ct = count($words);
  }
  /**
   * @param string $text
   * @return array(
   *   'words':[$,..],  // distinct matching words
   *   'ct':#,          // count of above
   *   'fit':#)         // closeness of fit, 1.00=perfect
   */
  public function match($text) {
    $text = SearchText::trimUpper($text);
    $words = SearchText::matchDistinct($text);
    $ct = count($words);
    $fit = ($ct == 0) ? 0 : $ct / max(count(explode(' ', $text)), $this->ct);
    return array(
      'text' => $text,
      'fit' => $fit,
      'ct' => $ct,
      'words' => $words);  
  }
  /**
   * @param string $text     // 'words and words and words';
   * @return array(word,..)  // ['words']
   */
  public function matchDistinct($text) {
    preg_match_all($this->pattern, $text, $matches);
    return array_unique($matches[0]);
  }
  //
  private function trimUpper($text) {
    return trim(strtoupper($text));
  }
  private function split($text) {
    $words = explode(' ', $text);
    $valids = array();
    foreach ($words as $word) {
      $this->fix($word);
      if ($this->isValid($word)) { 
        $valids[strtoupper($word)] = $word;
        if (count($valids) > 7) 
          break; 
      }
    }
    return array_keys($valids);
  }
  private function fix(&$word) {
    $word = str_replace('/', '', $word);
    $word = trim($word);
  } 
  private function isValid($word) {
    if (strlen($word) <= 2)
      return false;
    switch ($word) {
      case 'the':
      case 'this':
      case 'for':
        return false;
    }
    return true;
  }
}
/**
 * SearchTally
 */
class SearchTally {
  public $totalMatchCt = 0;
  public $highestMatchCt = 0;
  public $highestFit = 0;
  public $highestFitSubKey = null;
  public $highestFitText = null;
  public $distinctWords = array();
  public $key;
  //
  /**
   * @param(opt) string $key
   */
  public function __construct($key = null) {
    $this->key = $key;
  }
  /**
   * @param SearchText $search
   * @param string $text
   * @param(opt) string $subKey
   */
  public function tally($search, $text, $subKey = null) {
    $match = $search->match($text);
    $this->totalMatchCt += $match['ct'];
    $this->highestMatchCt = max($this->highestMatchCt, $match['ct']);
    if ($match['fit'] > $this->highestFit) { 
      $this->highestFit = $match['fit'];
      $this->highestFitSubKey = $subKey;
      $this->highestFitText = $match['text'];
    }
    foreach ($match['words'] as &$word) 
      $this->distinctWords[$word] = $word;
  }
}


