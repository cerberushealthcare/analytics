<?php
$sid = (isset($_GET['sid'])) ? $_GET['sid'] : 'image_text'; 

// Create an image that is 150x40
$gen_image = imagecreate(185, 40);

// Create color strings for black, white and gray
$white = imagecolorallocate ($gen_image, 0, 0, 0);
$black = imagecolorallocate ($gen_image, 255, 255, 255);
$gray  = imagecolorallocate ($gen_image, 128, 128, 128);

// Fill image with white color
imagefill($gen_image, 0, 0, $white);

session_start();
$img_font = "Verdana.ttf";

// put text in gray and offset by 1 with black for shadowed effect
//imagettftext($gen_image, 14, 0, 13, 28, $gray, $img_font, $_SESSION["image_text"][0]);
//                 img             size a  x   y    color     font
imagettftext($gen_image, 18, 0, 15, 27, $black, $img_font, $_SESSION[$sid][0]);

//imagettftext($gen_image, 14, 30, 36, 28, $gray, $img_font, $_SESSION["image_text"][1]);
imagettftext($gen_image, 18, 15, 40, 27, $black, $img_font, $_SESSION[$sid][1]);

//imagettftext($gen_image, 14, 330, 59, 28, $gray, $img_font, $_SESSION["image_text"][2]);
imagettftext($gen_image, 18, 355, 60, 27, $black, $img_font, $_SESSION[$sid][2]);

//imagettftext($gen_image, 14, 15, 83, 28, $gray, $img_font, $_SESSION["image_text"][3]);
imagettftext($gen_image, 18, 10, 85, 27, $black, $img_font, $_SESSION[$sid][3]);

//imagettftext($gen_image, 14, 5, 96, 28, $gray, $img_font, $_SESSION["image_text"][4]);
imagettftext($gen_image, 18, 5, 105, 27, $black, $img_font, $_SESSION[$sid][4]);

//imagettftext($gen_image, 14, 350, 119, 28, $gray, $img_font, $_SESSION["image_text"][5]);
imagettftext($gen_image, 18, 350, 125, 27, $black, $img_font, $_SESSION[$sid][5]);

imagettftext($gen_image, 18, 0, 145, 27, $black, $img_font, $_SESSION[$sid][6]);


// Send the picture to the browser
header("Content-type: image/png");
imagepng($gen_image);

// free server memory of image
imagedestroy($gen_image);
