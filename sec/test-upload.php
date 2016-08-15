<?php 
require_once 'inc/requireLogin.php';
?>
<html> 
<body>
  <form enctype="multipart/form-data" action="test-upload2.php" method="post">
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
    Choose a file to upload: <input name="scanfile[]" type="file" /><br>
    Choose a file to upload: <input name="scanfile[]" type="file" /><br>
    <br><input type='hidden' name='cid' value='123'><input type="submit" value="Upload" />
  </form> 
</body> 
</html>