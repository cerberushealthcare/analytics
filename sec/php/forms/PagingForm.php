<?php
require_once "php/forms/Form.php";
require_once "php/forms/utils/Paging.php";
require_once "php/data/ui/Anchor.php";

class PagingForm extends Form {
  
  // Constructor props
  public $baseUrl;
  
  // Helpers
  public $paging;  // Paging
  public $recordCount;  // number of records on this page
  public $more;  // true if more pages exist
  
  private $qsc;  // query string conjunctor (either "?" or "&")

  // Call once at page load
  protected function initialize($baseUrl, $sortKey, $sortDir, $maxRows) {
    $this->baseUrl = $baseUrl;
    $this->paging = new Paging($sortKey, $sortDir, $maxRows);
    $this->qsc = (strpos($baseUrl, "?") === false) ? "?" : "&";
  }
  
  public function getCurrentUrl() {
    return $this->baseUrl . $this->qsc . $this->paging->buildQueryStrings();
  }
  
  // Call after fetching rows
  protected function setRecordCount($rows) {
    $this->recordCount = sizeof($rows);
    if ($this->recordCount > $this->paging->maxRows) {
      $this->recordCount = $this->paging->maxRows;
      $this->more = true;
    } else {
      $this->more = false;
    }
  }

  // True if filter query strings supplied
  public function isSearching() {
    return $this->paging->filterSet;
  }
  
  // Labels
  public function recordNumbers($searchLabel = "Search Results: ") {
    $i = $this->paging->maxRows * ($this->paging->page - 1) + 1;
    $j = $i + $this->recordCount - 1;
    $a = ($this->isSearching() ? $searchLabel : "");
    if ($j == 0) {
      return $a . "No Records";
    } else if ($i == $j) {
      return $a . "Record " . $i;
    } else {
      return $a . "Records " . $i . " to " . $j;
    }
  }
  public function pageCount() {
    if ($this->paging->page > 1 || $this->more) {
      return " (p. " . $this->paging->page . ")";
    }
  }
  
  // HTML builders
  public function prevAnchorHtml() {
    if ($this->paging->page > 1) {
      $a = new Anchor($this->baseUrl . $this->qsc . $this->paging->buildQueryStrings(null, null, $this->paging->page - 1), "Prev");
      return $a->html("nav-prev");
    }
  }
  public function nextAnchorHtml() {
    if ($this->more) {
      $a = new Anchor($this->baseUrl . $this->qsc . $this->paging->buildQueryStrings(null, null, $this->paging->page + 1), "Next");
      return $a->html("nav-next");
    }
  }
  public function sortableHeader($field, $text, $colspan = 1) {
    $h = "<th";
    if ($colspan > 1) {
      $h .= " colspan=" . $colspan;
    }
    return $h . " class='" . $this->thClass($field) . "'>" . $this->sortAnchor($field, $text) . "</th>";
  } 
  public function thClass($field) {
    return ($field == $this->paging->sortKey) ? "sorted" : "";
  }
  public function sortAnchor($field, $text) {
    $h = "<a href='" . $this->baseUrl . $this->qsc;
    if ($field == $this->paging->sortKey) {
      $sd = -$this->paging->sortDirection;
      $h .= $this->paging->buildQueryStrings(null, $sd, 1);
      $h .= "' class='";
      if ($sd > 0) {
        $h .= "sort-d' title='Sort ascending'>";
      } else {
        $h .= "sort-a' title='Sort descending'>";
      }
    } else {
      $h .= $this->paging->buildQueryStrings($field, 1, 1);
      $h .= "' title='Sort ascending'>";
    }
    $h .= $text . "</a>";
    return $h;
  }
}

class OffsettingRow {
  
  public $trClass;  // "" or "offset"
  
  public function setTrClass($rowIndex) {
    $this->trClass = ($rowIndex % 2 == 0) ? "" : "offset";
  }
}
?>