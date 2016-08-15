<?php
class CrumbTrail {

	private $trail;

	public function __construct() {
		$this->trail = array();
	}

	public function push($url, $title) {
		$crumb = new Crumb($url, $title);
		$this->trail[] = $crumb;
	}

	public function html() {
    $prefix = substr($_SERVER['HTTP_HOST'], 0, 4);
    if ($prefix == "loca" || $prefix == "test") {
     $html = "";
    } else {
     $html = "<span style='font-weight:bold; color:red; font-size:20pt;'>YOU ARE IN THE LIVE SYSTEM</span> ";
    }
		for ($i = count($this->trail) - 1; $i >= 0; $i--) {
			$crumb = $this->trail[$i];
			$html .= "<a href='" . $crumb->url . "'>";
			$html .= $crumb->title;
			$html .="</a>";
			$html .= "&nbsp;&gt;&nbsp;";
		}
		return $html;
	}
}
class Crumb {
	
	public $url;
	public $title;

	public function __construct($url, $title) {
		$this->url = $url;
		$this->title = $title;
	}
}
?>
