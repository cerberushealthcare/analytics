<?php
/**
 * Name Parser
 * Adapted from http://alphahelical.com/code/misc/nameparse/
 */
class NameParser {
  //
  public $title;
  public $first;
  public $middle;
  public $last;
  public $suffix;
  /**
   *  Constructor
   * @param string $fullname 'Dr. Henry S. Collier Jr.'
   */
  public function __construct($fullname) {
    $out = NameParser::parse_name($fullname);
    $this->title = $out['title'];
    $this->first = $out['first'];
    $this->middle = $out['middle'];
    $this->last = $out['last'];
    $this->suffix = $out['suffix'];
  }
  //
  private static function norm_str($string) {
    return  trim(strtolower(
    str_replace('.','',$string)));
  }
  private static function in_array_norm($needle,$haystack) {
    return  in_array(NameParser::norm_str($needle),$haystack);
  }
  private static function parse_name($fullname) {
    $titles     = array('dr','miss','mr','mrs','ms','judge');
    $prefices   = array('ben','bin','da','dal','de','del','der','de','e','la','le','san','st','ste','van','vel','von');
    $suffices   = array('esq','esquire','jr','sr','2','ii','iii','iv');
    $pieces     = explode(',',preg_replace('/\s+/',' ',trim($fullname)));
    $n_pieces   = count($pieces);
    $out = array(
      'title' => null,
      'first' => null,
      'middle' => null,
      'last' => null,
      'suffix' => null);
    switch($n_pieces) {
      case  1:  // array(title first middles last suffix)
        $subp = explode(' ',trim($pieces[0]));
        $n_subp = count($subp);
        for($i = 0; $i < $n_subp; $i++) {
          $curr       = trim($subp[$i]);
          $next = ($i < $n_subp - 1) ? trim($subp[$i+1]) : null;

          if($i == 0 && NameParser::in_array_norm($curr,$titles)) {
            $out['title'] = $curr;
            continue;
          }

          if(!$out['first']) {
            $out['first'] = $curr;
            continue;
          }

          if($i == $n_subp-2 && $next && NameParser::in_array_norm($next,$suffices)) {
            if($out['last']) {
              $out['last']  .=  " $curr";
            }
            else {
              $out['last']  = $curr;
            }
            $out['suffix']    = $next;
            break;
          }

          if($i == $n_subp-1) {
            if($out['last']) {
              $out['last']  .=  " $curr";
            }
            else {
              $out['last']  = $curr;
            }
            continue;
          }

          if(NameParser::in_array_norm($curr,$prefices)) {
            if($out['last']) {
              $out['last']  .=  " $curr";
            }
            else {
              $out['last']  = $curr;
            }
            continue;
          }

          if($next == 'y' || $next == 'Y') {
            if($out['last']) {
              $out['last']  .=  " $curr";
            }
            else {
              $out['last']  = $curr;
            }
            continue;
          }

          if($out['last']) {
            $out['last']  .=  " $curr";
            continue;
          }

          if($out['middle']) {
            $out['middle']    .=  " $curr";
          }
          else {
            $out['middle']    = $curr;
          }
        }
        break;
      case  2:
        switch(NameParser::in_array_norm($pieces[1],$suffices)) {
          case  TRUE: // array(title first middles last,suffix)
            $subp = explode(' ',trim($pieces[0]));
            $n_subp = count($subp);
            for($i = 0; $i < $n_subp; $i++) {
              $curr       = trim($subp[$i]);
              $next       = trim($subp[$i+1]);

              if($i == 0 && NameParser::in_array_norm($curr,$titles)) {
                $out['title'] = $curr;
                continue;
              }

              if(!$out['first']) {
                $out['first'] = $curr;
                continue;
              }

              if($i == $n_subp-1) {
                if($out['last']) {
                  $out['last']  .=  " $curr";
                }
                else {
                  $out['last']  = $curr;
                }
                continue;
              }

              if(NameParser::in_array_norm($curr,$prefices)) {
                if($out['last']) {
                  $out['last']  .=  " $curr";
                }
                else {
                  $out['last']  = $curr;
                }
                continue;
              }

              if($next == 'y' || $next == 'Y') {
                if($out['last']) {
                  $out['last']  .=  " $curr";
                }
                else {
                  $out['last']  = $curr;
                }
                continue;
              }

              if($out['last']) {
                $out['last']  .=  " $curr";
                continue;
              }

              if($out['middle']) {
                $out['middle']    .=  " $curr";
              }
              else {
                $out['middle']    = $curr;
              }
            }
            $out['suffix']  = trim($pieces[1]);
            break;
          case  FALSE: // array(last,title first middles suffix)
            $subp = explode(' ',trim($pieces[1]));
            $n_subp = count($subp);
            for($i = 0; $i < $n_subp; $i++) {
              $curr       = trim($subp[$i]);
              $next       = trim($subp[$i+1]);

              if($i == 0 && NameParser::in_array_norm($curr,$titles)) {
                $out['title'] = $curr;
                continue;
              }

              if(!$out['first']) {
                $out['first'] = $curr;
                continue;
              }

              if($i == $n_subp-2 && $next &&
              NameParser::in_array_norm($next,$suffices)) {
                if($out['middle']) {
                  $out['middle']  .=  " $curr";
                }
                else {
                  $out['middle']  = $curr;
                }
                $out['suffix']    = $next;
                break;
              }

              if($i == $n_subp-1 && NameParser::in_array_norm($curr,$suffices)) {
                $out['suffix']    = $curr;
                continue;
              }

              if($out['middle']) {
                $out['middle']    .=  " $curr";
              }
              else {
                $out['middle']    = $curr;
              }
            }
            $out['last']  = $pieces[0];
            break;
        }
        unset($pieces);
        break;
          case  3:  // array(last,title first middles,suffix)
            $subp = explode(' ',trim($pieces[1]));
            $n_subp = count($subp);
            for($i = 0; $i < $n_subp; $i++) {
              $curr       = trim($subp[$i]);
              $next       = trim($subp[$i+1]);
              if($i == 0 && NameParser::in_array_norm($curr,$titles)) {
                $out['title'] = $curr;
                continue;
              }

              if(!$out['first']) {
                $out['first'] = $curr;
                continue;
              }

              if($out['middle']) {
                $out['middle']    .=  " $curr";
              }
              else {
                $out['middle']    = $curr;
              }
            }

            $out['last']        = trim($pieces[0]);
            $out['suffix']        = trim($pieces[2]);
            break;
          default:  // unparseable
            unset($pieces);
            break;
    }

    return $out;
  }

}
?>