<?php
/**
 * Chordbook 
 * @author Warren Hornsby
 */
$v = file_get_contents('version', FILE_USE_INCLUDE_PATH);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Chordbook</title>
  <meta charset='utf-8'>
  <meta name='viewport' content='user-scalable=no,width=device-width,initial-scale=1.0,maximum-scale=1.0'>
  <meta name='apple-mobile-web-app-capable' content='yes'>
  <meta name='apple-mobile-web-app-status-bar-style' content='black'>
  <link rel='apple-touch-icon-precomposed' href='icons/icon_60x60.png'>
  <link rel='apple-touch-startup-image' href='icons/splash_320x460.png'>
  <link rel='stylesheet' href='css/jquery.mobile-1.3.2.min.css'>
  <link rel='stylesheet' href='css/my-jquery-overrides.css?<?=$v?>'>
  <link rel='stylesheet' href='css/my.css?<?=$v?>'>
  <script type='text/javascript' src='js/jquery.js'></script>
  <script type='text/javascript' src='js/lcd.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/my-plugins.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/data/Inits.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/data/ChordList.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/data/SongList.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/data/ChordIconShape.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/ui/Pages.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/ui/Header.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/ui/Dialogs.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/ui/ChordIcon.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/jquery.mobile-1.3.2.min.js'></script>
</head>
<body>
  <div id='Home' data-role='page'>
    <div class='head'>
      <table>
        <tr>
          <td class='l'>&nbsp;</td>
          <th class='m'>Song List</th>
          <td class='r'><a class='plus' href='#'></a></td>
        </tr>
      </table>
    </div>  
    <div id='body-home' class='body'>
    </div>
  </div>
  <div id='Song' data-role='page'>
    <div class='head'>
      <table>
        <tr>
          <td class='l'><a class='back' href='#'></a></td>
          <th class='m'>Song</th>
          <td class='r'><a href='#' class='edit'></a></td>
        </tr>
      </table>
    </div>
    <div id='body-song' class='body working'>
    </div>
  </div>
  <div id='EditSong' data-role='page'>
    <div class='head'>
      <table>
        <tr>
          <td class='l'><a href='#'>Cancel</a></td>
          <th class='m'></th>
          <td class='r'><a href='#'>Save</a></td>
        </tr>
      </table>
    </div>
    <div id='body-edit-song' class='body'>
      <form id='edit-song-form'>
        <div id='edit-at-box'>
          <label for='edit-artist'>Artist</label>
          <input type='text' id='edit-artist' class='required' />
          <label for='edit-title'>Title</label>
          <input type='text' id='edit-title' class='required' />
        </div>
        <div id='edit-body-box'>
          <label for='edit-body'>Lyrics/Chords</label>
          <textarea id='edit-body' data-mini='true'></textarea>
        </div>
        <a id='edit-delete' href='#' data-role='button' data-theme='r'>Delete Song</a>
      </form>
    </div>
  </div>
  <div id='EditChord' data-role='page'>
    <div id='body-edit-chord' class='body'>
    </div>
  </div>
  <div id='pop-confirm' data-role='dialog'>
    <div data-role='content' data-theme='c'>
      <div class='text'>Are you sure?</div>
      <a href='#' data-role='button' class='yes' data-theme='r'>Yes</a>
      <a href='#' data-role='button' class='no' data-rel="back" data-theme='w'>Cancel</a>      
    </div>
  </div>
</body>
<script>
$(document).on('pageinit', function(event) {
  Pages.pageinit(event.target.id);
})
</script>
</html>