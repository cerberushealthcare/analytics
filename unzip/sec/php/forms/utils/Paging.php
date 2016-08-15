<?php
require_once "php/forms/Form.php";

/*
 * Encapsulates paging data for data tables
 */
class Paging {
  
  // Form variables
  public $sortKey;  // psk: sort field identifier; separate multi-columns with space
  public $sortDirection;  // psd: 1 (ascending) or -1 (descending)
  public $page;  // pp: int, start page number
  public $maxRows;  // pmr: int
  public $filter1;  // pf1...
  public $filter2;  // pf2...
  
  // Derived
  public $filterSet = false;  // true if a filter passed
  
  public function __construct($defaultSortKey, $defaultSortDirection = 1, $defaultMaxRows = 100) {
    $this->sortKey = Form::getFormVariable("psk", $defaultSortKey);
    $this->sortDirection = Form::getFormVariable("psd", $defaultSortDirection);
    $this->page = Form::getFormVariable("pp", 1);
    $this->maxRows = Form::getFormVariable("pmr", $defaultMaxRows);
    $this->setFilter(1,
        Form::getFormVariable("pf1"),
        Form::getFormVariable("pfe1"),
        Form::getFormVariable("pfv1"),
        Form::getFormVariable("pfc1")
        );
    $this->setFilter(2,
        Form::getFormVariable("pf2"),
        Form::getFormVariable("pfe2"),
        Form::getFormVariable("pfv2"),
        Form::getFormVariable("pfc1")
    );
  }
  
  public function setFilter($index, $name, $eq, $value, $conj = null) {
    if ($name == null) {
      return;
    }
    $filter = new Filter($name, $eq, $value, $conj);
    if ($index == 2) {
      $this->filter2 = $filter;
    } else {
      $this->filter1 = $filter;
    }
    $this->filterSet = true;
  }
  
  // Pass nulls to keep initialized values
  public function buildQueryStrings($psk = null, $psd = null, $pp = null, $pmr = null) {
    $qs = "psk=" . (($psk == null) ? $this->sortKey : $psk);
    $qs .= "&psd=" . (($psd == null) ? $this->sortDirection : $psd);
    $qs .= "&pp=" . (($pp == null) ? $this->page : $pp);
    $qs .= "&pmr=" . (($pmr == null) ? $this->maxRows : $pmr);
    if ($this->filter1 != null) {
      $qs .= $this->buildFilterQueryStrings(1, $this->filter1);
    }
    if ($this->filter2 != null) {
      $qs .= $this->buildFilterQueryStrings(2, $this->filter2);
    }
    return $qs;
  }
  
  private function buildFilterQueryStrings($index, $f) {
    $qs = "&pf" . $index . "=" . $f->name;
    $qs .= "&pfe" . $index . "=" . $f->eq;
    $qs .= "&pfv" . $index . "=" . $f->value;
    $qs .= "&pfc" . $index . "=" . $f->conj;
    return $qs;    
  }
  
  // Returns all built SQL clauses
  public function buildSql() {
    return $this->buildSqlWhere() . $this->buildSqlOrderBy() . $this->buildSqlLimit();
  }
 
  // Builds SQL "WHERE" clause
  public function buildSqlWhere() {
    $s = "(1=1";
    if ($this->filter1 != null) {
      $s = "(" . $this->buildSqlWhereFilter($this->filter1);
    }
    if ($this->filter2 != null) {
      $s .= $this->buildSqlWhereFilterConj($this->filter1) . $this->buildSqlWhereFilter($this->filter2);
    }
    return $s . ")";
  }
  
  // Builds SQL "ORDER BY" clause
  public function buildSqlOrderBy() {
    $a = explode(" ", $this->sortKey);
    $s = " ORDER BY ";
    for ($i = 0; $i < sizeof($a); $i++) {
      if ($i > 0) {
        $s .= ", ";
      }
      $s .= $a[$i] . (($this->sortDirection > 0) ? "" : " DESC");
    }
    return $s;
  }
  
  // Builds SQL "LIMIT" clause
  // NOTE! This will return one extra row beyond the maxRows spec, in order to determine whether or not a "next" link is valid.
  public function buildSqlLimit() {
    if ($this->page == 1) {
      return " LIMIT " . ($this->maxRows + 1); 
    } else {
      return " LIMIT " . ($this->maxRows * ($this->page - 1) . "," . ($this->maxRows + 1));
    }
  }
  
  private function buildSqlWhereFilter($f) {
    switch ($f->eq) {
      case Filter::EQ_BEGINS_WITH:
        return $f->name . " LIKE '" . $f->value . "%'";
      case Filter::EQ_CONTAINS:
        return $f->name . " LIKE '%" . $f->value . "%'";    
      case Filter::EQ_EQUALS:
        return $f->name . "='" . $f->value . "'";    
    }
  }
  
  private function buildSqlWhereFilterConj($f) {
    return $f == null ? " AND " : ($f->conj == Filter::CONJ_OR ? " OR " : " AND ");
  }
}

class Filter {
  
  public $name;  // pf#
  public $eq;  // pfe#: 0 (begins-with), 1 (contains), 2 (equals)
  public $value;  // pv#
  public $conj;  // pc#: 0 (and) 1 (or)
  
  const EQ_BEGINS_WITH = 0;
  const EQ_CONTAINS = 1;
  const EQ_EQUALS = 2;
  
  const CONJ_AND = 0;
  const CONJ_OR = 1; 

  public function __construct($name, $eq, $value, $conj = null) {
    $this->name = $name;
    $this->eq = $eq != null ? $eq : Filter::EQ_BEGINS_WITH;
    $this->value = $value;
    $this->conj = $conj !== null ? $conj : Filter::CONJ_AND;
  }
}
?>