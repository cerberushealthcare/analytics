<?php 
$url = 'https://www.papyrus-pms.com/pls/apex/f?p=2307:748:6842891281143:::748:P0_AGENCY_CLIENTID:489';
//setcookie('PCOOKIE', '0F77662B3B865DCE0E51A08640AC3532', 0, '/', 'www.papyrus-pms.com', false, true);
//print_r($_COOKIE);
header("Location: $url");
