<?php
require_once 'php/data/db/_util.php';
/**
 * Template Option
 */
class Option {
  //
	public $id;
	public $questionId;
	public $uid;
	public $desc;
	public $text;
	public $shape;
	public $coords;
	public $sortOrder;
	public $sync;
  public $cpt;
  public $trackCat;
	/**
	 * Constructor
	 */
	public function __construct($id, $questionId, $uid, $desc, $text, $shape, $coords, $sortOrder, $sync, $cpt, $trackCat) {
		$this->id = $id;
		$this->questionId = $questionId;
		$this->uid = $uid;
		$this->desc = $desc;
		$this->text = $text;
		$this->shape = $shape;
		$this->coords = $coords;
		$this->sortOrder = $sortOrder;
		$this->sync = $sync;
    $this->cpt = $cpt;
    $this->trackCat = $trackCat;
	}
	//
	// SQL builders
	//
  const SQL_FIELDS = 'option_id,question_id,uid,`desc`,text,shape,coords,sort_order,sync_id,cpt_code,track_cat';
	/**
	 * @param $escape: true to escape quotes
	 * @return string
	 */
	public function buildSqlInsert($escape) {
    $sql = 'INSERT INTO template_options VALUES(NULL';
    $sql .= ', ' . $this->questionId;
    $sql .= ', ' . quote($this->uid, $escape);
    $sql .= ', ' . quote($this->desc, $escape);
    $sql .= ', ' . quote($this->text, $escape);
    $sql .= ', ' . quote($this->shape);
    $sql .= ', ' . quote($this->coords, $escape);
    $sql .= ', ' . $this->sortOrder;
    $sql .= ', ' . quote($this->sync);
    $sql .= ', ' . quote($this->cpt);
    $sql .= ', ' . quote($this->trackCat);
    $sql .= ')';
	  return $sql;
	}
	//
	// Statics builders
	//
	/**
	 * @param array $row [field=>value,..]
	 * @return Option
	 */
	public static function fromRow($row) {
    if (! $row)
      return null;
    else
      return new Option(
        $row["option_id"],
        $row["question_id"],
        $row["uid"],
        $row["desc"],
        $row["text"],
        $row["shape"],
        $row["coords"],
        $row["sort_order"],
        $row["sync_id"],
        $row["cpt_code"],
        $row["track_cat"]
        );
	}
}
?>
