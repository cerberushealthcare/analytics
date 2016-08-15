<?php

function genImageValue() {

	$img_value = "";
   // Create a string that has 6 randomly generated characters
   for ($i=1; $i<8; $i++) {
      $img_value .= genRandomChar();
   }
   return $img_value;
}

function genRandomChar() {

// WGH fixes:
// it is retarded to include characters that cannot be distinguished
// such as O and 0, 1 and I, W and w, O and o, V and v

	$chars = "abcdeghkijkmnprstuvwxyz3478";
	return substr($chars, mt_rand(0, strlen($chars) - 1), 1);

//   mt_srand((double)microtime()*1000000);
//   $img_chr = mt_rand(1, 3);
//   switch ($img_chr) {
//      case 1:
//         // numbers
//         $img_chr = mt_rand(50, 57);
//         break;
//      case 2:
//         // upper case characters
//         $img_chr = mt_rand(65, 90);
//         break;
//      case 3:
//         // lower case characters
//         $img_chr = mt_rand(97, 122);
//         break;
//   }
//   return chr($img_chr);
}  

?>