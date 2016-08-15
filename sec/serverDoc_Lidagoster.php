<?php 
if (isset($_POST["fmt"])) {
	$fmt = $_POST["fmt"];
} else {
	$fmt = "doc";
}
if (isset($_POST["img"])) {
  $img = $_POST["img"];
} else {
  $img = "";
}
if (isset($_POST["img2"])) {
  $img2 = $_POST["img2"];
} else {
  $img2 = "";
}
if (isset($_POST["sigimg"])) {
  $sigimg = $_POST["sigimg"];
} else {
  $sigimg = "";
}
if (isset($_POST["signame"])) {
  $signame = str_replace("<br>"," \par ", $_POST["signame"]);
} else {
  $signame = "";
}
if (isset($_POST["noTag"])) {
  $noTag = $_POST["noTag"] == "1";
} else {
  $noTag = false;
}
$head = stripslashes($_POST["head"]);
$head = str_replace("\par", "\line", $head);
$headAlign = "\qr";
if (isset($_POST["lefthead"])) {
  if ($_POST["lefthead"] == "1") {
    $headAlign = "";
  }
}
$dos = date("m-d-y", strtotime($_POST["docDos"]));

// Convert header image to RTF
if ($img != "") {
  $imgrtf = "{\\pict\n\\jpegblip\n" . bin2hex(file_get_contents($img)) . "}";
}
if ($img2 != "") {
  $imgrtf2 = "{\\pict\n\\jpegblip\n" . bin2hex(file_get_contents($img2)) . "}";
}
// Convert signature image to RTF
if ($sigimg != "") {
  $sigimgrtf = "{\\pict\n\\jpegblip\n" . bin2hex(file_get_contents($sigimg)) . "}";
} else {
  $sigimgrtf = "\par \par";
}
header("Content-Type: application/" . $fmt); 
header("Content-Disposition: attachment; filename=" . $_POST["cn"] . "." . $fmt);
?>
{\rtf1\ansi\ansicpg1252\deff0{\fonttbl{\f0\froman\fprq2\fcharset0 Times New Roman;}{\f1\fswiss\fcharset0 Arial;}{\colortbl;\red0\green0\blue0;\red128\green128\blue128;\cbackgroundone\ctint255\cshade217\red217\green217\blue217;}}
{\widowctrl}
\titlepg {\headerf 
{\rtf1\ansi\deff0
\trowd
\cellx6500
\cellx10000
{\b\fs32 Advanced Psychiatric Perspectives, PLLC 
\line }{\f0\fs20 1776 Broadway, Suite 1200, New York, NY 10019
\line Tel: 212-707-8662   Fax: 212-582-0888}{\fs20 
\line\line\b Lidia Lidagoster, MD
\line 917-923-0123\b
}
\cell
\qr\plain\f0\fs18 <?=$head ?>
\cell
\row
} 
{\b\fs30 
\line PSYCHIATRY NOTE                                       <?=$dos ?> 
}
}
{\header\pard<?=$headAlign ?>\plain\f0\fs18 <?=stripslashes($_POST["head"]) ?>\pard\par\pard}
{\footer\pard\qr\plain\f0\fs18 <?=$dos ?>\par\p\pard\par\pard}
\viewkind4\uc1\pard\lang1033\f0\fs22
<?=stripslashes($_POST["doc"]) ?>
\par \par <?=$sigimgrtf ?> \par \par <?=$signame ?>
}