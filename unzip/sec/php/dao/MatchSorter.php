<?php
/*
 * MatchSorter
 * For sorting dtos by number of matches to supplied pattern. Example:
 * 
 * $sorter = new MatchSorter($pattern, 1);
 * if ($sorter->words > 1) {
 *   foreach ($pars as $par) {
 *     $sorter->add($par, $par->id);
 *     foreach ($par->q->opts as $opt) {
 *       $sorter->tally($par->id, $opt->desc);
 *     }
 *   }
 *   $pars = $sorter->sort();
 * }
 */
class MatchSorter {
  
  public $words;  // number of words in pattern 
  public $dtos;   // sorted dtos
  public $more;   // if sort(max) exceeded
  
  private $pattern;
  private $method;
  private $distinct = true; 
  private $matchesByKey = array();
  private $dtosByKey = array();
  private $highMatchesOnly = 0; 
  private $highMatch = 0;
  
  // Tally methods
  const METHOD_HIGHEST = 0;  // Highest of individual tallies within grouping key  
  const METHOD_SUM = 1;      // Sum of individual tallies within grouping key
  
  // pattern: "/word1|word2|word3/"
  // highMatchesOnly: 1 to keep only dtos matching highest (e.g. all the 3-hitters), 2 to keep top two (e.g. the 3- and 2-hitters). Not recommended for METHOD_SUM tallies.
  // distinct: true=count only distinct keywords, false=count all
  public function __construct($pattern, $highMatchesOnly = 0, $distinct = true, $tallyMethod = MatchSorter::METHOD_HIGHEST) { 
    $this->pattern = $pattern;
    $this->words = count(explode("|", $pattern));
    $this->highMatchesOnly = $highMatchesOnly;
    $this->distinct = $distinct;
    $this->method = $tallyMethod;
  }
  
  // Add dto to grouping key
  public function add($dto, $key) {
    $this->dtosByKey[$key][] = $dto;
    if (count($this->dtosByKey[$key]) == 1) {
      $this->matchesByKey[$key] = 0;
    }
  }
  
  // Tally text matches for grouping key
  public function tally($key, $text) {
    $count = MatchSorter::countMatches($this->pattern, $text, $this->distinct);
    if ($this->method == MatchSorter::METHOD_HIGHEST) {
      $this->matchesByKey[$key] = $this->higher($this->matchesByKey[$key], $count);
      $this->highMatch = $this->higher($this->highMatch, $count);
    } else {
      $this->matchesByKey[$key] += $count; 
    }
  }
  
  // Return dtos sorted by match count
  public function sort($max = 50) {  
    arsort($this->matchesByKey);
    $this->dtos = array();
    $floor = ($this->highMatchesOnly == 0) ? 0 : $this->highMatch - ($this->highMatchesOnly - 1);
    foreach($this->matchesByKey as $key => $count) {
      if ($count >= $floor) {
        array_splice($this->dtos, count($this->dtos), 0, $this->dtosByKey[$key]);
        if (count($this->dtos) > $max) {
          $this->more = true;
          return $this->dtos;
        }
      }
    }
    $this->more = false;
    return $this->dtos;
  }

  private function higherMatchCount($matchCount, $text, $distinct = true) {
    return MatchSorter::higher($matchCount, MatchSorter::countMatches($this->pattern, $text, $this->distinct));
  }

  // Static functions
  public static function countMatches($pattern, $text, $distinct = true) {
    preg_match_all($pattern, $text, $matches);
    $matches = ($distinct) ? array_unique($matches[0]) : $matches[0];
    return count($matches); 
  }
  public static function higher($a, $b) {
    return ($a > $b) ? $a : $b;
  }
}
?>