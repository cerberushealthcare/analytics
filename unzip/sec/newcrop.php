<?php
$rf = isset($_GET['rf']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
  <head>
    <title>NewCrop ePrescribing</title>
  </head>
  <frameset rows="35px,1*" frameborder='0' framespacing='0'>
    <frame src="newcrop-title.php" />
<?php if ($rf) { ?>
    <frameset cols="250px,1*" frameborder='0' framespacing='0'>
      <frame src="newcrop-refill.php?<?=$_SERVER['QUERY_STRING'] ?>" />
<?php } ?>    
      <frame src="newcrop-body.php?<?=$_SERVER['QUERY_STRING'] ?>" />
<?php if ($rf) { ?>
    </frameset> 
<?php } ?>    
  </frameset>
</html>