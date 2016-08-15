<?php
class HomeDao {

  public static function getLatestMikeTip() {
    try {
      $sql = "SELECT * FROM `phpbb`.`phpbb_posts` where forum_id=18 group by topic_id order by post_id desc";
      $res = HomeDao::fetch($sql);
      $row = self::getRow($res);
    } catch (Exception $e) {
      $row = null;      
    }
    return $row; 
  }
  public static function getLatestMikeTips($count = 2) {
    $rows = array();
    try {
      $sql = "SELECT * FROM `phpbb`.`phpbb_posts` where forum_id=18 group by topic_id order by post_id desc";
      $res = HomeDao::fetch($sql);
      for ($i = 0; $i < $count; $i++) {
        $row = self::getRow($res);
        if ($row)
          $rows[] = $row;
      }
    } catch (Exception $e) {
    }
    return $rows; 
  }
  private static function getRow($res) {
    if ($res == null)
      return null;
    $row = mysql_fetch_array($res, MYSQL_ASSOC);
    if ($row) {
      $row["post_time"] = date("d-M-Y", $row["post_time"]);
      $row["post_text"] = nl2br($row["post_text"]);
    }
    return $row;
  }
  private static function fetch($sql) {
    $res = HomeDao::query($sql);
    if (! $res || mysql_num_rows($res) < 1) 
      return null;
    return $res;
  }
  private static function query($sql) {
    $conn = mysql_connect("localhost", "webuser", "click01");
    if ($conn) {
      $res = mysql_query($sql);
      mysql_close($conn);
      return $res;
    }
  }
}
?>