<html>
  <head>
    <style>
BODY {
  background-color:#008E80;
  margin:0;
  font-family:Arial;
  font-size:11pt;
}
DIV#body {
  border:3px solid black;
  border-top:none;
  border-left:none;
  padding:20px 5px 20px 5px;
  background:#FEFF9F;
  width:240px;
}
DIV#title {
  margin:0 1em;
  padding-bottom:0.2em;
  border-bottom:3px solid #008E80;
}
UL {
  margin-top:1em;
  margin-bottom:0;
  margin-left:1em;
}
LI.len {
  list-style-type:none;
  margin-top:1em;
}
    </style>
  </head>
  <body>
    <div id='body'>
      <div id='title'>
        <b>Requested Refills<br>
        from Clicktate Message</b>
      </div>
      <ul id='medDiv' style=''>
      </ul>
    </div>
  </body>
  <script>
setTimeout(ld, 1000);
function ld() {
  var refills = <?=stripslashes($_GET['rf'])?>;
  var ul = document.getElementById('medDiv');
  var h = [];
  for (var length in refills) {
    var meds = refills[length];
    h.push('<li class="len"><b>Length: ' + length  + '</b></li>');
    h.push('<li>' + meds.join('</li><li>') + '</li>');
  } 
  ul.innerHTML = h.join('');
  ul.style.marginBottom = '40px';
}
  </script>  
</html>