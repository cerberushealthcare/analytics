<?php if (isset($msg)) { ?>
  <div class="message" id="message-div">
    <table border=0 cellpadding=0 cellspacing=0>
      <tr>
        <td style="text-align:center; font-size:11pt; font-family:Arial; font-weight:bold; color:#008C7B; padding-top:4px; padding-bottom:4px">
          <?=$msg ?>
        </td>
      </tr>
    </table>
  </div>
<?php } ?>
<?php if (isset($omsg)) { ?>
          <p style="font-weight:bold; padding:0"><?=$omsg ?></p>
<?php } ?>
<?php if (isset($errors)) { ?>
  <div class="message" id="error-div">
    <table border=0 cellpadding=0 cellspacing=0>
      <tr>
        <td style="color:red; font-size:10pt; font-family:Arial; font-weight:bold; padding-top:4px; padding-bottom:4px; ">
          <?php foreach ($errors as $id => $emsg) { ?>
            <?=$emsg ?><br>
          <?php } ?>
          <br>
        </td>
      </tr>
    </table>
  </div>
<?php } ?>
  