<?php
$email = isset($_GET['e']) ? $_GET['e'] : null;
if ($email) {
  try {
    $f = @fopen("remove-emails.csv", "a");
    $a = array();
    $a[] = date("d-M-Y, g:i:s A");
    $a[] = $email;
    $a[] = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "";
    $a[] = isset($_SERVER["QUERY_STRING"]) ? $_SERVER["QUERY_STRING"] : "";
    $a[] = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "";
    $a[] = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
    $a[] = isset($_SERVER["HTTP_COOKIE"]) ? $_SERVER["HTTP_COOKIE"] : "";
    $line = "\"" . implode("\",\"", $a) . "\"\r\n";
    @fwrite($f, $line);
    @fclose($f);
    echo 'Your email has been added to our remove list.';
  } catch (Exception $e) {
  }
} 
?>
<?php $title = 'Clicktate - Email Management' ?>
<?php include "inc/hheader.php" ?>
<div id="body" style="background:white">
  <div class="content center">
    <h1>Email Subscription Management</h1>
  </div>
  <div class="wm">
    <form method="post" action="remove.php">
      <div class="l" style="padding-top:30px;margin-top:10px;">
        <label>Email</label>
        <input id="email" type="text" size="35" name="email" />
        <div style="padding-top:20px">
          <a class="tour video cm" style="padding-top:10px;padding-bottom:10px" href="javascript:submit()">Remove Me</a>
        </div>
        <div style="margin-top:30px;">
          <p style="font-size:9pt">
            <b>Note:</b>
            It may take up to two business days for complete removal. 
            We appreciate your patience.
          </p>
          <p style="font-size:9pt">
            If you continue to receive unwanted emails please verify that these are not
            being forwarded from a secondary (aliased) email address.
          </p>
        </div>
      </div>
    </form>
  </div>
</div>
<?php include "inc/hfooter.php" ?>
