<?php
require_once "php/data/json/_util.php";

class JQuestionTemplate {

	public $id;
	public $bt;
	public $at;
	public $btms;
	public $atms;
	public $btmu;
	public $atmu;
	public $listType;
	public $break;
	public $test;
	public $actions;
	public $hide;
	public $outDataJson;

	public function __construct($id, $bt, $at, $btms, $atms, $btmu, $atmu, $listType, $break, $test, $actions, $hide, $outData, $mix) {
		$this->id = $id;
		$this->bt = $bt;
		$this->at = $at;
		$this->btms = $btms;
		$this->atms = $atms;
		$this->btmu = $btmu;
		$this->atmu = $atmu;
		$this->listType = $listType;
		$this->break = $break;
		$this->test = $test;
		$this->actions = $actions;
		$this->hide = $hide;
		$this->setOutDataJson($outData, $mix);
	}
	
	// Make array out of single string
	private function setOutDataJson($outData, $mix) {
    if ($outData != null) {  // vitals:pulse=ouid,x=y ...or... table:outFn(args),outFn(arg)
      $a = explode(":", $outData); // vitals  pulse=ouid,x=y
      if (count($a) > 1) {
        $pks = null;
        $dtid = $a[0];  // vitals
        $out = $a[1];   // pulse=ouid,x=y
        if (strpos($out, '=') === false) {
          $parsed = DataDao::parseOutFunctions($out, $mix !== null);
          $out = $parsed["out"];
          $pks = $parsed["pk"];  // ["4"]
        }
        $b = explode(",", $out);  // pulse=ouid  x=y
        for ($i = 0; $i < count($b); $i++) {
          $c = explode("=", $b[$i]);  // pulse  ouid
          $b[$i] = qq($c[0], $c[1]);  // "pulse":"ouid"
        }
        $pk = jsonencode($pks);
        $data = cb(implode(",", $b));  // {"pulse":"ouid","x":"y"}
        $all = cb(qqo("pk", $pk) . C . qqo("cols", $data));  // {"pk":["4"],"cols":{"pulse":"ouid","x":"y"}}
        $this->outDataJson = cb(qqo($dtid, $all));  // {"vitals":{"pk":["4"],"cols":{"pulse":"ouid","x":"y"}}
        return;
      }
    }
    $this->outDataJson = null;
	}
	
	public function out() {
    $out = "";
    $out = nqq($out, "id", $this->id);
    $out = nqq($out, "bt", $this->bt);
    $out = nqq($out, "at", $this->at);
    $out = nqq($out, "btms", $this->btms);
    $out = nqq($out, "atms", $this->atms);
    $out = nqq($out, "btmu", $this->btmu);
    $out = nqq($out, "atmu", $this->atmu);
    $out = nqqo($out, "lt", $this->listType);
    $out = nqqo($out, "brk", $this->break);
    $out = nqq($out, "test", $this->test);
    $out = nqqa($out, "actions", $this->actions);
    $out = nqqo($out, "hide", $this->hide);
    $out = nqqo($out, "out", $this->outDataJson);
    return cb($out);    
	}
}
?>