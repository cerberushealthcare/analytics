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
$headAlign = "\qr";
if (isset($_POST["lefthead"])) {
  if ($_POST["lefthead"] == "1") {
    $headAlign = "";
  }
}

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
header("Content-Disposition: attachment; filename=\"" . $_POST["cn"] . "." . $fmt . "\"");
?>
{\rtf1\ansi\ansicpg1252\deff0{\fonttbl{\f0\froman\fprq2\fcharset0 Times New Roman;}{\f1\fswiss\fcharset0 Arial;}{\colortbl;\red0\green0\blue0;\red128\green128\blue128;\cbackgroundone\ctint255\cshade217\red217\green217\blue217;}}
{\widowctrl}
<? if ($img != "") { ?>
\titlepg {\headerf \pard\qr\plain\f0\fs18 \qc <?=$imgrtf ?>\par\pard\plain\f0\fs18 Specializing in Pain Management and Addiction Medicine\pard\par\pard<?=$headAlign ?> <?=stripslashes($_POST["head"]) ?>\pard\par\pard}<? if ($img2 != "") { ?>{\header\pard\qr\plain\f0\fs18 \qc <?=$imgrtf2 ?>\par\pard<?=$headAlign ?> <?=stripslashes($_POST["head"]) ?>\pard\par\pard}<? } else { ?>{\header\pard<?=$headAlign ?>\plain\f0\fs18 <?=stripslashes($_POST["head"]) ?>\pard\par\pard}<? } ?>
<? } else { ?>
{\header\pard<?=$headAlign ?>\plain\f0\fs18 <?=stripslashes($_POST["head"]) ?>\pard\par\pard}
<? } ?>
<? if (! $noTag) { ?>
{\footer\pard\qc\plain\f0\fs15 This note created at clicktate.com\par\p\pard\par\pard}
<? } ?>
\viewkind4\uc1\pard\lang1033\f0\fs22
<?=stripslashes($_POST["doc"]) ?>
\par \par <?=$sigimgrtf ?> \par \par <?=$signame ?>
}